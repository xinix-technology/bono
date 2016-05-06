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
        $bundle = $this->getMock(Rest::class, ['search', 'create', 'read', 'update', 'delete', 'getSchema'], [$this->app]);

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/'))
        ]);

        try {
            $bundle->dispatch($context);
            $this->fail('Must throw exception');
        } catch(\Exception $e) {
            if ($e instanceof \PHPUnit_Framework_AssertionFailedError) {
                throw $e;
            }
        }

        $context = Injector::getInstance()->resolve(Context::class, [
            'request' => new Request('GET', new Uri('http', 'localhost', 80, '/'))
        ]);
        $context['@bodyParser'] = $this->getMock(stdClass::class);

        $bundle->dispatch($context);
    }
}