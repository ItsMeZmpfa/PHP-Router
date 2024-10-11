<?php

require_once __DIR__ . "/../TestRouter.php";
require_once __DIR__ ."/../Dummy/DummyController.php";
class RouteUrlTest extends \PHPUnit\Framework\TestCase
{

    public function testIssue253()
    {
        TestRouter::get('/', 'DummyController@method1');
        TestRouter::get('/page/{id?}', 'DummyController@method1');
        TestRouter::get('/test-output', function () {
            return 'return value';
        });

        TestRouter::debugNoReset('/page/22', 'get');
        $this->assertEquals('/page/{id?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::debugNoReset('/', 'get');
        $this->assertEquals('/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        $output = TestRouter::debugOutput('/test-output', 'get');
        $this->assertEquals('return value', $output);

        TestRouter::router()->reset();
    }

    public function testUrls()
    {
        // Match normal route on alias
        TestRouter::get('/', 'DummyController@method1');

        TestRouter::get('/about', 'DummyController@about');



        // Pretend to load page
        TestRouter::debugNoReset('/', 'get');


        /* Find by Controller*/

        // Should match /about/
        $this->assertEquals('/about/', TestRouter::getUrl('DummyController@about')->getPath());

        // Should match /
        $this->assertEquals('/', TestRouter::getUrl('DummyController@method1')->getPath());

        /*Find By Url */
        // Should match /about/
        $this->assertEquals('/about/', TestRouter::getUrl('/about')->getPath());

        // Should match /
        $this->assertEquals('/', TestRouter::getUrl('/')->getPath());


        TestRouter::reset();

    }

    public function testSimilarUrls()
    {
        TestRouter::reset();
        // Match normal route on alias
        TestRouter::get('/url11', 'DummyController@method1');
        TestRouter::get('/url22', 'DummyController@method2');
        TestRouter::get('/url33', 'DummyController@method2');

        TestRouter::debugNoReset('/url22', 'get');

        $this->assertEquals(TestRouter::getUrl('/url22'), TestRouter::getUrl());

        TestRouter::router()->reset();
    }

    public function testOptionalParameters()
    {
        TestRouter::get('/aviso/legal', 'DummyController@method1');
        TestRouter::get('/aviso/{aviso}', 'DummyController@method1');
        TestRouter::get('/pagina/{pagina}', 'DummyController@method1');
        TestRouter::get('/{pagina?}', 'DummyController@method1');

        TestRouter::debugNoReset('/aviso/optional', 'get');
        $this->assertEquals('/aviso/{aviso}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::debugNoReset('/pagina/optional', 'get');
        $this->assertEquals('/pagina/{pagina}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::debugNoReset('/optional', 'get');
        $this->assertEquals('/{pagina?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertNotEquals('/aviso/{aviso}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::debugNoReset('/avisolegal', 'get');
        $this->assertEquals('/{pagina?}/', TestRouter::router()->getRequest()->getLoadedRoute()->getUrl());

        TestRouter::router()->reset();
    }


}