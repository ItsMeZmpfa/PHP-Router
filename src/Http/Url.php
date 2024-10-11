<?php

namespace Demo\Http;

use Demo\Exceptions\MalformedUrlException;
use JsonSerializable;

class Url implements JsonSerializable
{
    /**
     * @var string|null
     */
    private ?string $originalUrl = null;

    /**
     * @var string|null
     */
    private ?string $path = null;

    /**
     * @var array
     */
    private array $params = [];


    /**
     * Url constructor.
     *
     * @param ?string  $url
     * @throws MalformedUrlException
     */
    public function __construct(?string $url)
    {
        $this->originalUrl = $url;
        $this->parse($url, true);
    }

    /**
     * @throws MalformedUrlException
     */
    public function parse(?string $url, bool $setOriginalPath = false): self
    {
        if ($url !== null) {
            $data = $this->parseUrl($url);


            if (isset($data['path']) === true) {
                $this->setPath($data['path']);

            }

            if (isset($data['query']) === true) {
                $this->setQueryString($data['query']);
            }
        }

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    /**
     * Get path from url
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path ?? '/';
    }

    /**
     * Set the url path
     *
     * @param string $path
     * @return static
     */
    public function setPath(string $path): self
    {
        $this->path = rtrim($path, '/') . '/';

        return $this;
    }


    /**
     * @throws MalformedUrlException
     */
    public function parseUrl(string $url, int $component = -1): array
    {
        $encodedUrl = preg_replace_callback(
            '/[^:\/@?&=#]+/u',
            static function ($matches): string {
                return urlencode($matches[0]);
            },
            $url
        );



        $parts = parse_url($encodedUrl, $component);



        if ($parts === false) {
            throw new MalformedUrlException(sprintf('Failed to parse url: "%s"', $url));
        }

        return array_map('urldecode', $parts);
    }

    public function setQueryString(string $queryString): self
    {
        $params = [];
        parse_str($queryString, $params);

        if (count($params) > 0) {
            return $this->setParams($params);
        }

        return $this;
    }

    /**
     * Set the url params
     *
     * @param array $params
     * @return static
     */
    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }
}