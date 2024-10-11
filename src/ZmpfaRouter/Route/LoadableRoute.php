<?php

namespace Demo\ZmpfaRouter\Route;


use Demo\Http\Request;
use Demo\ZmpfaRouter\Route\Interface\ILoadableRoute;

abstract class LoadableRoute extends Route implements ILoadableRoute
{

    /**
     * @var string
     */
    protected string $url;

    /**
     * @var string
     */
    protected ?string $name = null;

    /**
     * @var string|null
     */
    protected ?string $regex = null;

    public function setUrl(string $url): ILoadableRoute
    {
        $this->url = ($url === '/') ? '/' : '/' . trim($url, '/') . '/';

        $parameters = [];
        if (strpos($this->url, $this->paramModifiers[0]) !== false) {

            $regex = sprintf(static::PARAMETERS_REGEX_FORMAT, $this->paramModifiers[0], $this->paramOptionalSymbol, $this->paramModifiers[1]);

            if ((bool)preg_match_all('/' . $regex . '/u', $this->url, $matches) !== false) {
                $parameters = array_fill_keys($matches[1], null);
            }
        }

        $this->parameters = $parameters;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Find url that matches method, parameters or name.
     * Used when calling the url() helper.
     *
     * @param string|null $method
     * @param string|array|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl(?string $method = null, $parameters = null, ?string $name = null): string
    {
        $url = $this->getUrl();

        /* Create the param string - {parameter} */
        $param1 = $this->paramModifiers[0] . '%s' . $this->paramModifiers[1];

        /* Create the param string with the optional symbol - {parameter?} */
        $param2 = $this->paramModifiers[0] . '%s' . $this->paramOptionalSymbol . $this->paramModifiers[1];

        /* Replace any {parameter} in the url with the correct value */

        $params = $this->getParameters();

        foreach (array_keys($params) as $param) {

            if ($parameters === '' || (is_array($parameters) === true && count($parameters) === 0)) {
                $value = '';
            } else {
                $p = (array)$parameters;
                $value = array_key_exists($param, $p) ? $p[$param] : $params[$param];

                /* If parameter is specifically set to null - use the original-defined value */
                if ($value === null && isset($this->originalParameters[$param]) === true) {
                    $value = $this->originalParameters[$param];
                }
            }

            if (stripos($url, $param1) !== false || stripos($url, $param) !== false) {
                /* Add parameter to the correct position */
                $url = str_ireplace([sprintf($param1, $param), sprintf($param2, $param)], (string)$value, $url);
            } else {
                /* Parameter aren't recognized and will be appended at the end of the url */
                $url .= $value . '/';
            }
        }

        $url = rtrim('/' . ltrim($url, '/'), '/') . '/';

        return $url;
    }

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName(string $name): bool
    {
        return strtolower((string)$this->name) === strtolower($name);
    }

    public function matchRegex(Request $request, $url): ?bool
    {
        /* Match on custom defined regular expression */
        if ($this->regex === null) {
            return null;
        }

        $parameters = [];
        if ((bool)preg_match($this->regex, $url, $parameters) !== false) {
            $this->setParameters($parameters);

            return true;
        }

        return false;
    }
}