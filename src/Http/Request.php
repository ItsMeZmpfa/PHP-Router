<?php

namespace Demo\Http;

use Demo\Http\Input\InputHandler;
use Demo\ZmpfaRouter\Route\Interface\ILoadableRoute;

class Request
{
    public const REQUEST_TYPE_GET = 'get';
    public const REQUEST_TYPE_POST = 'post';
    public const REQUEST_TYPE_PUT = 'put';
    public const REQUEST_TYPE_PATCH = 'patch';
    public const REQUEST_TYPE_OPTIONS = 'options';
    public const REQUEST_TYPE_DELETE = 'delete';
    public const REQUEST_TYPE_HEAD = 'head';

    public const CONTENT_TYPE_JSON = 'application/json';
    public const CONTENT_TYPE_FORM_DATA = 'multipart/form-data';
    public const CONTENT_TYPE_X_FORM_ENCODED = 'application/x-www-form-urlencoded';

    public const FORCE_METHOD_KEY = '_method';

    /**
     * All request-types
     * @var string[]
     */
    public static array $requestTypes = [
        self::REQUEST_TYPE_GET,
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_OPTIONS,
        self::REQUEST_TYPE_DELETE,
        self::REQUEST_TYPE_HEAD,
    ];

    /**
     * Post request-types.
     * @var string[]
     */
    public static array $requestTypesPost = [
        self::REQUEST_TYPE_POST,
        self::REQUEST_TYPE_PUT,
        self::REQUEST_TYPE_PATCH,
        self::REQUEST_TYPE_DELETE,
    ];

    /**
     * Request method
     * @var string
     */
    protected string $method;

    /**
     * @return string|null
     */

    /**
     * Current request url
     * @var Url
     */
    protected Url $url;

    /**
     * @var array
     */
    protected array $loadedRoutes = [];

    /**
     * Request host
     * @var string|null
     */
    protected ?string $host;

    /**
     * Input handler
     * @var InputHandler
     */
    protected InputHandler $inputHandler;

    /**
     * Server headers
     * @var array
     */
    protected array $headers = [];

    /**
     * Request ContentType
     * @var string
     */
    protected string $contentType;

    public function __construct()
    {
        foreach ($_SERVER as $key => $value) {
            $this->headers[strtolower($key)] = $value;
            $this->headers[str_replace('_', '-', strtolower($key))] = $value;
        }

        $this->setHost($this->getHeader('http-host'));

        $this->setContentType((string)$this->getHeader('content-type'));
        $this->setMethod((string)($_POST[static::FORCE_METHOD_KEY] ?? $this->getHeader('request-method')));

        $this->inputHandler = new InputHandler($this);
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = strtolower($method);
    }

    /**
     * @return Url
     */
    public function getUrl(): Url
    {
        return $this->url;
    }

    /**
     * Added loaded route
     *
     * @param ILoadableRoute $route
     * @return static
     */
    public function addLoadedRoute(ILoadableRoute $route): self
    {
        $this->loadedRoutes[] = $route;
        return $this;
    }

    /**
     * Get all loaded routes
     *
     * @return array
     */
    public function getLoadedRoutes(): array
    {
        return $this->loadedRoutes;
    }

    /**
     * @param string|null $host
     */
    public function setHost(?string $host): void
    {
        // Strip any potential ports from hostname
        if (strpos((string)$host, ':') !== false) {
            $host = strstr($host, strrchr($host, ':'), true);
        }

        $this->host = $host;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @param Url $url
     */
    public function setUrl(Url $url): void
    {
        $this->url = $url;

    }

    /**
     * Get loaded route
     * @return ILoadableRoute|null
     */
    public function getLoadedRoute(): ?ILoadableRoute
    {
        return (count($this->loadedRoutes) > 0) ? end($this->loadedRoutes) : null;
    }

    /**
     * Copy url object
     *
     * @return Url
     */
    public function getUrlCopy(): Url
    {
        return clone $this->url;
    }

    /**
     * Get header value by name
     *
     * @param string $name Name of the header.
     * @param  mixed|null  $defaultValue Value to be returned if header is not found.
     * @param bool $tryParse When enabled the method will try to find the header from both from client (http) and server-side variants, if the header is not found.
     *
     * @return string|null
     */
    public function getHeader(string $name, mixed $defaultValue = null, bool $tryParse = true): ?string
    {
        $name = strtolower($name);
        $header = $this->headers[$name] ?? null;

        if ($tryParse === true && $header === null) {
            if (strpos($name, 'http-') === 0) {
                // Trying to find client header variant which was not found, searching for header variant without http- prefix.
                $header = $this->headers[str_replace('http-', '', $name)] ?? null;
            } else {
                // Trying to find server variant which was not found, searching for client variant with http- prefix.
                $header = $this->headers['http-' . $name] ?? null;
            }
        }

        return $header ?? $defaultValue;
    }

    /**
     * Get request content-type
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * Set request content-type
     * @param string $contentType
     * @return $this
     */
    protected function setContentType(string $contentType): self
    {
        if (strpos($contentType, ';') > 0) {
            $this->contentType = strtolower(substr($contentType, 0, strpos($contentType, ';')));
        } else {
            $this->contentType = strtolower($contentType);
        }

        return $this;
    }

    /**
     * Returns true when request-method is type that could contain data in the page body.
     *
     * @return bool
     */
    public function isPostBack(): bool
    {
        return in_array($this->getMethod(), static::$requestTypesPost, true);
    }

    /**
     * Get input class
     * @return InputHandler
     */
    public function getInputHandler(): InputHandler
    {
        return $this->inputHandler;
    }


}