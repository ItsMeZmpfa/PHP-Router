<?php

namespace Demo\ZmpfaRouter\Route\Interface;

use Closure;
use Demo\Http\Request;
use Demo\ZmpfaRouter\Router;

interface IRoute
{

    /**
     * Set callback
     *
     * @param  array|string|Closure  $callback
     * @return static
     */
    public function setCallback(array|string|Closure $callback): self;

    /**
     * @return string|callable
     */
    public function getCallback(): callable|string;

    /**
     * Set allowed request methods
     *
     * @param array $methods
     * @return static
     */
    public function setRequestMethods(array $methods): self;

    /**
     * Get allowed request methods
     *
     * @return array
     */
    public function getRequestMethods(): array;

    /**
     * Method called to check if a domain matches
     *
     * @param string $url
     * @param Request $request
     * @return bool
     */
    public function matchRoute(string $url, Request $request): bool;

    /**
     * Get parameters
     *
     * @param array $parameters
     * @return static
     */
    public function setParameters(array $parameters): self;

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Called when route is matched.
     * Returns class to be rendered.
     *
     * @param  Request  $request
     * @param  Router  $router
     * @return string|null
     */
    public function renderRoute(Request $request, Router $router): ?string;

    /**
     * Get class from callback
     *
     * @return string|null
     */
    public function getClass(): ?string;

    /**
     * Return active method from callback
     *
     * @return string|null
     */
    public function getMethod(): ?string;



}