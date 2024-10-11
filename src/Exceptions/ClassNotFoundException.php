<?php

namespace Demo\Exceptions;

use Demo\Exceptions\NotFoundException;
use Throwable;

class ClassNotFoundException extends NotFoundException
{
    /**
     * @var string
     */
    protected string $class;

    /**
     * @var string|null
     */
    protected ?string $method = null;

    public function __construct(string $class, ?string $method = null, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->class = $class;
        $this->method = $method;
    }

    /**
     * Get class name
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get method
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

}