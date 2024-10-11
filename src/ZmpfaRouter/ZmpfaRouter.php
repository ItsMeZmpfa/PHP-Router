<?php

namespace Demo\ZmpfaRouter;

use Demo\Http\Request;
use Demo\Http\Url;
use Demo\ZmpfaRouter\ClassLoader\Interface\IClassLoader;
use Demo\ZmpfaRouter\Route\Interface\IRoute;
use Demo\ZmpfaRouter\Route\RouteUrl;
use Exception;


class ZmpfaRouter
{

    /**
     * Router instance
     * @var Router
     */
    protected static ?Router $router = null;


    public static function start(): void
    {
        echo static::router()->start();
    }

    public static function match(array $requestMethods, string $url, $callback): IRoute
    {
        $route = new RouteUrl($url, $callback);
        $route->setRequestMethods($requestMethods);

        return static::router()->addRoute($route);
    }


    public static function router(): Router
    {

        /*Create New Router Instance if empty */
        if (static::$router === null) {
            static::$router = new Router();
        }

        return static::$router;
    }

    public static function get(string $url, $callback): IRoute
    {
        return static::match([Request::REQUEST_TYPE_GET], $url, $callback);
    }

    /**
     * Route the given url to your callback on POST request method.
     *
     * @param  string  $url
     * @param  string|array|Closure  $callback
     * @return IRoute
     */
    public static function post(string $url, $callback): IRoute
    {
        return static::match([Request::REQUEST_TYPE_POST], $url, $callback);
    }


    /**
     * Route the given url to your callback on PUT request method.
     *
     * @param  string  $url
     * @param  string|array|Closure  $callback
     * @return IRoute
     */
    public static function put(string $url, $callback): IRoute
    {
        return static::match([Request::REQUEST_TYPE_PUT], $url, $callback);
    }

    /**
     * Route the given url to your callback on PATCH request method.
     *
     * @param  string  $url
     * @param  string|array|Closure  $callback
     * @param  array|null  $settings
     * @return IRoute
     */
    public static function patch(string $url, $callback, array $settings = null): IRoute
    {
        return static::match([Request::REQUEST_TYPE_PATCH], $url, $callback);
    }

    /**
     * Route the given url to your callback on OPTIONS request method.
     *
     * @param  string  $url
     * @param  string|array|Closure  $callback
     * @param  array|null  $settings
     * @return IRoute
     */
    public static function options(string $url, $callback, array $settings = null): IRoute
    {
        return static::match([Request::REQUEST_TYPE_OPTIONS], $url, $callback);
    }

    /**
     * Route the given url to your callback on DELETE request method.
     *
     * @param  string  $url
     * @param  string|array|Closure  $callback
     * @return IRoute
     */
    public static function delete(string $url, $callback): IRoute
    {
        return static::match([Request::REQUEST_TYPE_DELETE], $url, $callback);
    }

    /**
     * Get the request
     *
     * @return Request
     */
    public static function request(): Request
    {
        return static::router()->getRequest();
    }

    /**
     * Get url for a route by using either name/alias, class or method name.
     *
     * The name parameter supports the following values:
     * - Route name
     * - Controller/resource name (with or without method)
     * - Controller class name
     *
     * When searching for controller/resource by name, you can use this syntax "route.name@method".
     * You can also use the same syntax when searching for a specific controller-class "MyController@home".
     * If no arguments is specified, it will return the url for the current loaded route.
     *
     * @param string|null $name
     * @param  array|string|null  $parameters
     * @param array|null $getParams
     * @return Url
     */
    public static function getUrl(?string $name = null, array|string $parameters = null, ?array $getParams = null): Url
    {
        try {
            return static::router()->getUrl($name, $parameters, $getParams);
        } catch (Exception $e) {
            return new Url('/');
        }
    }

    /**
     * Set custom class-loader class used.
     * @param IClassLoader $classLoader
     */
    public static function setCustomClassLoader(IClassLoader $classLoader): void
    {
        static::router()->setClassLoader($classLoader);
    }
}