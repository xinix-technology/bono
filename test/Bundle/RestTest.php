<?php

namespace Bono\Test\Bundle;

use Bono\Test\BonoTestCase;
use Bono\Bundle\Rest;
use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Uri;
use ROH\Util\Injector;

class RestTest extends BonoTestCase
{
    public function testConstruct()
    {
        $bundle = $this->getMockBuilder(Rest::class)
            ->setMethods(['search', 'create', 'read', 'update', 'delete', 'getSchema'])
            ->setConstructorArgs([$this->app])
            ->getMock();

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/'))
        ]);

        try {
            $bundle->dispatch($context);
            $this->fail('Must throw exception');
        } catch (\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
            $this->assertTrue(true);
        }

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/'))
        ]);
        $context['@bodyParser'] = $this->createMock(\stdClass::class);

        $bundle->dispatch($context);
    }
}
