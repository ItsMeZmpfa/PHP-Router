<?php


class TestRouter extends \Demo\ZmpfaRouter\ZmpfaRouter
{
    public function __construct()
    {
        static::request()->setHost('testhost.com');
    }

    public static function reset(): void
    {
        static::$router = null;
    }

    public static function debugNoReset(string $testUrl, string $testMethod = 'get'): void
    {
        $request = static::request();

        $request->setUrl((new \Demo\Http\Url($testUrl)));
        $request->setMethod($testMethod);

        static::start();
    }

    public static function debugOutput(string $testUrl, string $testMethod = 'get', bool $reset = true): string
    {
        $response = null;

        // Route request
        ob_start();
        static::debug($testUrl, $testMethod, $reset);
        $response = ob_get_clean();

        // Return response
        return $response;
    }

    public static function debug(string $testUrl, string $testMethod = 'get', bool $reset = true): void
    {
        try {
            static::debugNoReset($testUrl, $testMethod);
        } catch (\Exception $e) {
            throw $e;
        }

    }
}