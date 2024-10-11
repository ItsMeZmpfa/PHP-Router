<?php

use Demo\Http\Request;

class DummyLoadableRoute extends \Demo\ZmpfaRouter\Route\LoadableRoute
{
    public function matchRoute(string $url, Request $request): bool
    {
        return false;
    }
}