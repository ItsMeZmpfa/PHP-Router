<?php

require_once __DIR__ . "/../TestRouter.php";
require_once __DIR__ ."/../Dummy/DummyController.php";
class RequestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @throws \Demo\Exceptions\NotFoundException
     */
    protected function processHeader($name, $value, callable $callback)
    {
        global $_SERVER;

        $_SERVER[$name] = $value;

        $router = TestRouter::router();
        $router->reset();

        $request = $router->getRequest();

        $callback($request);

        // Reset everything
        $_SERVER[$name] = null;
        $router->reset();
    }

    public function testContentTypeParse()
    {
        global $_SERVER;

        // tests normal content-type

        $contentType = 'application/x-www-form-urlencoded';

        $this->processHeader('content_type', $contentType, function(\Demo\Http\Request $request) use($contentType) {
            $this->assertEquals($contentType, $request->getContentType());
        });

        // tests special content-type with encoding

        $contentTypeWithEncoding = 'application/x-www-form-urlencoded; charset=UTF-8';

        $this->processHeader('content_type', $contentTypeWithEncoding, function(\Demo\Http\Request $request) use($contentType) {
            $this->assertEquals($contentType, $request->getContentType());
        });
    }

}