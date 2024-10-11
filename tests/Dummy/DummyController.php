<?php

class DummyController
{
    public function index()
    {

    }


    public function method1()
    {

    }

    public function method2()
    {

    }

    public function method3(): string
    {
        return 'method3';
    }

    public function param($params = null): void
    {
        echo join(', ', func_get_args());
    }

    public function getTest(): void
    {
        echo 'getTest';
    }

    public function postTest(): void
    {
        echo 'postTest';
    }

    public function putTest(): void
    {
        echo 'putTest';
    }

}