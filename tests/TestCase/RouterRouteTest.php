<?php
require_once __DIR__ . "/../TestRouter.php";
require_once __DIR__ ."/../Dummy/DummyController.php";

class RouterRouteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Issue #421: Incorrectly optional character in route
     *
     * @throws Exception
     */
    public function testOptionalCharacterRoute()
    {
        $result = false;
        TestRouter::get('/api/v1/users/{userid}/projects/{id}/pages/{pageid?}', function () use (&$result) {
            $result = true;
        });

        TestRouter::debug('/api/v1/users/1/projects/8399421535/pages/43/', 'get');

        $this->assertTrue($result);
    }

    public function testPost()
    {
        TestRouter::post('/my/test/url', 'DummyController@method1');
        TestRouter::debug('/my/test/url', 'post');

        $this->assertTrue(true);
    }

    public function testPut()
    {
        TestRouter::put('/my/test/url', 'DummyController@method1');
        TestRouter::debug('/my/test/url', 'put');

        $this->assertTrue(true);
    }

    public function testDelete()
    {
        TestRouter::delete('/my/test/url', 'DummyController@method1');
        TestRouter::debug('/my/test/url', 'delete');

        $this->assertTrue(true);
    }

    public function testSimpleParam()
    {
        TestRouter::get('/test-{param1}', 'DummyController@param');
        $response = TestRouter::debugOutput('/test-param1', 'get');

        $this->assertEquals('param1', $response);
    }

    public function testParametersWithDashes()
    {

        $defaultVariable = null;

        TestRouter::get('/my/{path}', function ($path = 'working') use (&$defaultVariable) {
            $defaultVariable = $path;
        });

        TestRouter::debug('/my/hello-motto-man');

        $this->assertEquals('hello-motto-man', $defaultVariable);

    }

    /**
     * @throws Exception
     */
    public function testParameterDefaultValue()
    {

        $defaultVariable = null;

        TestRouter::get('/my/{path?}', function ($path = 'working') use (&$defaultVariable) {
            $defaultVariable = $path;
        });

        TestRouter::debug('/my/');

        $this->assertEquals('working', $defaultVariable);

    }

    public function testSameRoutes()
    {
        TestRouter::get('/recipe', 'DummyController@method1');
        TestRouter::post('/recipe', 'DummyController@method2');

        TestRouter::debugNoReset('/recipe', 'post');
        TestRouter::debug('/recipe', 'get');

        $this->assertTrue(true);
    }

}