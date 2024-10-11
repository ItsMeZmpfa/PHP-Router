<?php

namespace Demo\ZmpfaRouter\Route;


use Demo\Http\Request;

class RouteUrl extends LoadableRoute
{

    public function __construct(string $url, $callback)
    {
        $this->setUrl($url);
        $this->setCallback($callback);
    }


    /**
     * @param  string  $url
     * @param  Request  $request
     * @return bool
     */
    public function matchRoute(string $url, Request $request): bool
    {
        /* Match global regular-expression for route */
        $regexMatch = $this->matchRegex($request, $url);

        if ($regexMatch === false) {
            return false;
        }

        /* Parse parameters from current route */
        $parameters = $this->parseParameters($this->url, $url, $request);

        /* If no custom regular expression or parameters was found on this route, we stop */
        if ($regexMatch === null && $parameters === null) {
            return false;
        }

        /* Set the parameters */
        $this->setParameters((array)$parameters);

        return true;
    }
}