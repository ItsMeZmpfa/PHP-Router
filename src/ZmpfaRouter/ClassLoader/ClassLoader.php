<?php

namespace Demo\ZmpfaRouter\ClassLoader;

use Demo\Exceptions\ClassNotFoundException;
use Demo\ZmpfaRouter\ClassLoader\Interface\IClassLoader;

class ClassLoader implements IClassLoader
{

    /**
     * @inheritDoc
     * @throws ClassNotFoundException
     */
    public function loadClass(string $class)
    {
        if (class_exists($class) === false) {
            throw new ClassNotFoundException($class, null, sprintf('Class "%s" does not exist', $class), 404, null);
        }

        return new $class();
    }

    /**
     * @inheritDoc
     */
    public function loadClassMethod($class, string $method, array $parameters)
    {
        return (string)call_user_func_array([$class, $method], array_values($parameters));
    }

    /**
     * @inheritDoc
     */
    public function loadClosure(callable $closure, array $parameters)
    {
        return (string)call_user_func_array($closure, array_values($parameters));
    }
}