<?php

namespace Demo\ZmpfaRouter\Route;

use Closure;
use Demo\Exceptions\ClassNotFoundException;
use Demo\Http\Request;
use Demo\ZmpfaRouter\Route\Interface\IRoute;
use Demo\ZmpfaRouter\Router;

abstract class Route implements IRoute
{

    protected const PARAMETERS_REGEX_FORMAT = '%s([\w]+)(\%s?)%s';
    protected const PARAMETERS_DEFAULT_REGEX = '[\w-]+';


    /**
     * @var string|callable|null
     */
    protected $callback;

    protected array $requestMethods = [];

    protected array $parameters = [];
    protected array $originalParameters = [];

    /**
     * If enabled parameters containing null-value
     * will not be passed along to the callback.
     *
     * @var bool
     */
    protected bool $filterEmptyParams = true;

    /**
     * Default regular expression used for parsing parameters.
     * @var string|null
     */
    protected ?string $defaultParameterRegex = null;
    protected string $paramModifiers = '{}';
    protected string $paramOptionalSymbol = '?';
    protected string $urlRegex = '/^%s\/?$/u';
    protected ?IRoute $parent = null;

    /**
     * If true the last parameter of the route will include ending trail/slash.
     * @var bool
     */
    protected bool $slashParameterEnabled = false;


    /**
     * Set callback
     *
     * @param  array|string|Closure  $callback
     * @return static
     */
    public function setCallback(array|string|\Closure $callback): IRoute
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return callable|string
     */
    public function getCallback(): callable|string
    {
        return $this->callback;
    }

    /**
     * Set allowed request methods
     *
     * @param array $methods
     * @return static
     */
    public function setRequestMethods(array $methods): IRoute
    {
        $this->requestMethods = $methods;

        return $this;
    }

    /**
     * Get allowed request methods
     *
     * @return array
     */
    public function getRequestMethods(): array
    {
        return $this->requestMethods;
    }

    /**
     * Set parameters
     *
     * @param array $parameters
     * @return static
     */
    public function setParameters(array $parameters): IRoute
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        /* Sort the parameters after the user-defined param order, if any */
        $parameters = [];

        if (count($this->originalParameters) !== 0) {
            $parameters = $this->originalParameters;
        }

        return array_merge($parameters, $this->parameters);
    }

    /**
     * Render route
     *
     * @param  Request  $request
     * @param  Router  $router
     * @return string|null
     * @throws ClassNotFoundException
     */
    public function renderRoute(Request $request, Router $router): ?string
    {

        $callback = $this->getCallback();

        if ($callback === null) {
            return null;
        }


        $parameters = $this->getParameters();


        /* Filter parameters with null-value */
        if ($this->filterEmptyParams === true) {
            $parameters = array_filter($parameters, static function ($var): bool {
                return ($var !== null);
            });
        }

        /* Render callback function */
        if (is_callable($callback) === true) {

            /* Load class from type hinting */
            if (is_array($callback) === true && isset($callback[0], $callback[1]) === true) {
                $callback[0] = $router->getClassLoader()->loadClass($callback[0]);
            }

            /* When the callback is a function */

            return $router->getClassLoader()->loadClosure($callback, $parameters);
        }

        $controller = $this->getClass();
        $method = $this->getMethod();

        $className = $controller;

        $class = $router->getClassLoader()->loadClass($className);

        if ($method === null) {
            $controller[1] = '__invoke';
        }

        if (method_exists($class, $method) === false) {
            throw new ClassNotFoundException($className, $method, sprintf('Method "%s" does not exist in class "%s"', $method, $className), 404, null);
        }

        return $router->getClassLoader()->loadClassMethod($class, $method, $parameters);
    }

    /**
     * Get Class from Callback
     * @return string|null
     */
    public function getClass(): ?string
    {
        if (is_array($this->callback) === true && count($this->callback) > 0) {
            return $this->callback[0];
        }

        if (is_string($this->callback) === true && strpos($this->callback, '@') !== false) {
            $tmp = explode('@', $this->callback);

            return $tmp[0];
        }

        return null;
    }

    /**
     * Get Active Method from Callback
     * @return string|null
     */
    public function getMethod(): ?string
    {
        if (is_array($this->callback) === true && count($this->callback) > 1) {
            return $this->callback[1];
        }

        if (is_string($this->callback) === true && strpos($this->callback, '@') !== false) {
            $tmp = explode('@', $this->callback);

            return $tmp[1];
        }

        return null;
    }
    protected function parseParameters($route, $url, Request $request, $parameterRegex = null): ?array
    {
        $regex = (strpos($route, $this->paramModifiers[0]) === false) ? null :
            sprintf
            (
                static::PARAMETERS_REGEX_FORMAT,
                $this->paramModifiers[0],
                $this->paramOptionalSymbol,
                $this->paramModifiers[1]
            );

        // Ensures that host names/domains will work with parameters
        if ($route[0] === $this->paramModifiers[0]) {
            $url = '/' . ltrim($url, '/');
        }

        $urlRegex = '';
        $parameters = [];

        if ($regex === null || (bool)preg_match_all('/' . $regex . '/u', $route, $parameters) === false) {
            $urlRegex = preg_quote($route, '/');
        } else {

            foreach (preg_split('/((\.?-?\/?){[^' . $this->paramModifiers[1] . ']+' . $this->paramModifiers[1] . ')/', $route) as $key => $t) {

                $regex = '';

                if ($key < count($parameters[1])) {

                    $name = $parameters[1][$key];

                    /* If custom regex is defined, use that */
                    if (isset($this->where[$name]) === true) {
                        $regex = $this->where[$name];
                    } else {
                        $regex = $parameterRegex ?? $this->defaultParameterRegex ?? static::PARAMETERS_DEFAULT_REGEX;
                    }

                    $regex = sprintf('((\/|-|\.)(?P<%2$s>%3$s))%1$s', $parameters[2][$key], $name, $regex);
                }

                $urlRegex .= preg_quote($t, '/') . $regex;
            }
        }

        // Get name of last param
        if (trim($urlRegex) === '' || (bool)preg_match(sprintf($this->urlRegex, $urlRegex), $url, $matches) === false) {
            return null;
        }

        $values = [];

        if (isset($parameters[1]) === true) {

            $lastParams = [];

            foreach ((array)$parameters[1] as $i => $name) {



                $values[$name] = (isset($matches[$name]) === true && $matches[$name] !== '') ? $matches[$name] : null;
            }

            $values += $lastParams;
        }

        $this->originalParameters = $values;

        return $values;
    }

}