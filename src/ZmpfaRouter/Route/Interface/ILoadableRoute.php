<?php

namespace Demo\ZmpfaRouter\Route\Interface;


interface ILoadableRoute extends IRoute
{

    /**
     * Get url
     * @return string
     */
    public function getUrl(): string;

    /**
     * Set url
     * @param string $url
     * @return static
     */
    public function setUrl(string $url): self;


    /**
     * Find url that matches method, parameters or name.
     *
     * @param string|null $method
     * @param array|string|null $parameters
     * @param string|null $name
     * @return string
     */
    public function findUrl(?string $method = null, $parameters = null, ?string $name = null): string;

    /**
     * Check if route has given name.
     *
     * @param string $name
     * @return bool
     */
    public function hasName(string $name): bool;
}