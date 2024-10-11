<?php

namespace Demo\ZmpfaRouter;

use Demo\Exceptions\HttpException;
use Demo\Exceptions\NotFoundException;
use Demo\Http\Request;
use Demo\Http\Url;
use Demo\ZmpfaRouter\ClassLoader\ClassLoader;
use Demo\ZmpfaRouter\Route\Interface\ILoadableRoute;
use Demo\ZmpfaRouter\Route\Interface\IRoute;
use Exception;
use Demo\ZmpfaRouter\ClassLoader\Interface\IClassLoader;

class Router
{

    /**
     * All added routes
     * @var array
     */
    protected array $routes = [];

    /**
     * List of processed routes
     * @var array|ILoadableRoute[]
     */
    protected array $processedRoutes = [];

    /**
     * Defines all data from current processing route.
     * @var ILoadableRoute
     */
    protected ILoadableRoute $currentProcessingRoute;

    /**
     * Current request
     * @var Request
     */
    protected Request $request;

    /**
     * Class loader instance
     * @var IClassLoader
     */
    protected IClassLoader $classLoader;

    public function __construct()
    {
        $this->reset();
    }

    /*
     * Resets the router by reloading request and clearing all routes Data
     */
    /**
     * @throws NotFoundException
     */
    public function reset(): void
    {

        try {
            $this->request = new Request();
        } catch (Exception $e) {
            throw new NotFoundException();
        }

        $this->routes = [];

        $this->processedRoutes = [];

        $this->classLoader = new ClassLoader();
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function start(): ?string
    {
        /* Loop through each Routes */
        $this->loadRoutes();

        $output = $this->routeRequest();

        return $output;
    }

    /**
     *  Add route to the List
     * @param  IRoute  $route
     * @return IRoute
     */
    public function addRoute(IRoute $route): IRoute
    {
        //add Route in Routes Stack
        $this->routes[] = $route;

        return $route;
    }

    /**
     * Load Routes
     * @return void
     */
    public function loadRoutes(): void
    {
        /* Loop through each route-request */
        $this->processRoutes($this->routes);
    }

    /**
     * Process to added routes
     * @param  array  $routes
     * @return void
     */
    protected function processRoutes(array $routes): void
    {
        foreach ($routes as $route) {

            if ($route instanceof ILoadableRoute === true) {

                /* Add the route to the map */
                $this->processedRoutes[] = $route;
            }
        }

    }

    /**
     * Routes the request
     *
     * @return string|null
     * @throws Exception
     */
    public function routeRequest(): ?string
    {


        $methodNotAllowed = null;

        try {
            $url = $this->request->getUrl()->getPath();

            /* @var $route ILoadableRoute */
            foreach ($this->processedRoutes as $key => $route) {


                /* Add current processing route to constants */
                $this->currentProcessingRoute = $route;

                /* If the route matches */
                if ($route->matchRoute($url, $this->request) === true) {


                    /* Check if request method matches */
                    if (count($route->getRequestMethods()) !== 0 && in_array($this->request->getMethod(), $route->getRequestMethods(), true) === false) {

                        // Only set method not allowed is not already set
                        if ($methodNotAllowed === null) {
                            $methodNotAllowed = true;
                        }

                        continue;
                    }

                    $methodNotAllowed = false;

                    $this->request->addLoadedRoute($route);


                    $routeOutput = $route->renderRoute($this->request, $this);

                    return $routeOutput;

                }
            }

        } catch (Exception $e) {
            throw new NotFoundException();
        }

        if ($methodNotAllowed === true) {
            $message = sprintf('Route "%s" or method "%s" not allowed.', $this->request->getUrl()->getPath(), $this->request->getMethod());
            throw new NotFoundException($message, 405);
        }

        if (count($this->request->getLoadedRoutes()) === 0) {


            throw new NotFoundException();
        }

        return null;
    }

    /**
     * Set class loader
     *
     * @param IClassLoader $loader
     */
    public function setClassLoader(IClassLoader $loader): void
    {
        $this->classLoader = $loader;
    }

    /**
     * Get class loader
     *
     * @return IClassLoader
     */
    public function getClassLoader(): IClassLoader
    {
        return $this->classLoader;
    }

    /**
     * Get current request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Get url for a route by using either name/alias, class or method name.
     *
     * The name parameter supports the following values:
     * - Route name
     * - Controller/resource name (with or without method)
     * - Controller class name
     *
     * You can also use the same syntax when searching for a specific controller-class "MyController@home".
     * If no arguments is specified, it will return the url for the current loaded route.
     *
     * @param string|null $name
     * @param string|array|null $parameters
     * @param array|null $getParams
     * @return Url
     */
    public function getUrl(?string $name = null, $parameters = null, ?array $getParams = null): Url
    {
        if ($name === '' && $parameters === '') {
            return new Url('/');
        }

        /* Only merge $_GET when all parameters are null */
        $getParams = ($name === null && $parameters === null && $getParams === null) ? $_GET : (array)$getParams;


        /* Return current route if no options has been specified */
        if ($name === null && $parameters === null) {
            return $this->request
                ->getUrlCopy()
                ->setParams($getParams);
        }

        $loadedRoute = $this->request->getLoadedRoute();

        /* If nothing is defined and a route is loaded we use that */
        if ($name === null && $loadedRoute !== null) {
            return $this->request->getUrlCopy()->parse($loadedRoute->findUrl($loadedRoute->getMethod(), $parameters, $name))->setParams($getParams);
        }

        if ($name !== null) {
            /* We try to find a match on the given name */
            $route = $this->findRoute($name);
         //   var_dump($this->request->getUrlCopy()->parse($route->findUrl($route->getMethod(), $parameters, $name))->setParams($getParams));


            if ($route !== null) {
                return $this->request->getUrlCopy()->parse($route->findUrl($route->getMethod(), $parameters, $name))->setParams($getParams);
            }
        }

        /* Using @ is most definitely a controller@method or alias@method */
        if (is_string($name) === true && strpos($name, '@') !== false) {
            [$controller, $method] = explode('@', $name);

            /* Loop through all the routes to see if we can find a match */

            /* @var $route ILoadableRoute */
            foreach ($this->processedRoutes as $processedRoute) {

                /* Check if the route contains the name/alias */
                if ($processedRoute->hasName($controller) === true) {
                    return $this->request->getUrlCopy()->parse($processedRoute->findUrl($method, $parameters, $name))->setParams($getParams);
                }


            }
        }

        /* No result so we assume that someone is using a hardcoded url and join everything together. */
        $url = trim(implode('/', array_merge((array)$name, (array)$parameters)), '/');
        $url = (($url === '') ? '/' : '/' . $url . '/');

        return $this->request->getUrlCopy()->parse($url)->setParams($getParams);
    }

    /**
     * Find route by alias, class, callback or method.
     *
     * @param string $name
     * @return ILoadableRoute|null
     */
    public function findRoute(string $name): ?ILoadableRoute
    {
        foreach ($this->processedRoutes as $route) {

            /* Check if the name matches with a name on the route. Should match either router alias or controller alias. */
            if ($route->hasName($name) === true) {
                return $route;
            }


            /* Using @ is most definitely a controller@method or alias@method */
            if (strpos($name, '@') !== false) {
                [$controller, $method] = array_map('strtolower', explode('@', $name));

                if ($controller === strtolower((string)$route->getClass()) && $method === strtolower((string)$route->getMethod())) {
                    return $route;
                }
            }

            /* Check if callback matches (if it's not a function) */
            $callback = $route->getCallback();
            if (is_string($callback) === true && is_callable($callback) === false && strpos($name, '@') !== false && strpos($callback, '@') !== false) {

                /* Check if the entire callback is matching */
                if (strpos($callback, $name) === 0 || strtolower($callback) === strtolower($name)) {
                    return $route;
                }

                /* Check if the class part of the callback matches (class@method) */
                if (strtolower($name) === strtolower($route->getClass())) {
                    return $route;
                }
            }
        }
        return null;
    }
}