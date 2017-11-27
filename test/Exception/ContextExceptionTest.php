<?php
namespace Bono\Test\Exception;

use PHPUnit\Framework\TestCase;
use Bono\Exception\ContextException;

class ContextExceptionTest extends TestCase
{
    public function testConstructWithStatus()
    {
        $e = new ContextException(500, 'wow', null);
        $this->assertEquals(500, $e->getStatusCode());
    }
}
