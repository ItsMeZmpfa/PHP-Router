<?php

require_once __DIR__ . "/../TestRouter.php";
require_once __DIR__ ."/../Dummy/DummyController.php";
class InputHandlerTest extends \PHPUnit\Framework\TestCase
{
    protected $names = [
        'Lester',
        'Michael',
        'Franklin',
        'Trevor',
    ];

    protected $brands = [
        'Samsung',
        'Apple',
        'HP',
        'Canon',
    ];

    protected $sodas = [
        0 => 'Pepsi',
        1 => 'Coca Cola',
        2 => 'Harboe',
        3 => 'Mountain Dew',
    ];

    protected $day = 'monday';

    public function testPost()
    {
        global $_POST;

        $_POST = [
            'names' => $this->names,
            'day' => $this->day,
            'sodas' => $this->sodas,
        ];

        $router = TestRouter::router();
        $router->reset();
        $router->getRequest()->setMethod('post');

        $handler = TestRouter::request()->getInputHandler();

        $this->assertEquals($this->names, $handler->value('names'));
        $this->assertEquals($this->names, $handler->all(['names'])['names']);
        $this->assertEquals($this->day, $handler->value('day'));
        $this->assertInstanceOf(\Demo\Http\Input\InputItem::class, $handler->find('day'));
        $this->assertInstanceOf(\Demo\Http\Input\InputItem::class, $handler->post('day'));
        $this->assertInstanceOf(\Demo\Http\Input\InputItem::class, $handler->find('day', 'post'));

        // Check non-existing and wrong request-type
        $this->assertCount(1, $handler->all(['non-existing']));
        $this->assertEmpty($handler->all(['non-existing'])['non-existing']);
        $this->assertNull($handler->value('non-existing'));
        $this->assertNull($handler->find('non-existing'));
        $this->assertNull($handler->value('names', null, 'get'));
        $this->assertNull($handler->find('names', 'get'));
        $this->assertEquals($this->sodas, $handler->value('sodas'));

        $objects = $handler->find('names');

        $this->assertInstanceOf(\Demo\Http\Input\InputItem::class, $objects);
        $this->assertCount(4, $objects);

        /* @var $object \Demo\Http\Input\InputItem */
        foreach($objects as $i => $object) {
            $this->assertInstanceOf(\Demo\Http\Input\InputItem::class, $object);
            $this->assertEquals($this->names[$i], $object->getValue());
        }

        // Reset
        $_POST = [];
    }

}