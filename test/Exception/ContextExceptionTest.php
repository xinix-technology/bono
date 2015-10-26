<?php
namespace Bono\Test\Exception;

use PHPUnit_Framework_TestCase;
use Bono\Exception\ContextException;

class ContextExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testConstructWithStatus()
    {
        $e = new ContextException(500, 'wow', null);
        $this->assertEquals(500, $e->getStatusCode());
    }
}
