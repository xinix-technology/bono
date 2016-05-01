<?php
namespace Bono\Test\Http;

use PHPUnit_Framework_TestCase;
use Bono\Http\Stream;
use Bono\Exception\BonoException;

class StreamTest extends PHPUnit_Framework_TestCase {
    public function testClose()
    {
        $stream = new Stream();
        $this->assertTrue($stream->isOpen());
        $stream->close();
        $this->assertFalse($stream->isOpen());
    }

    public function testDetach()
    {
        $stream = new Stream();
        $resource = $stream->detach();
        $this->assertTrue(is_resource($resource));
        $this->assertFalse($stream->isOpen());
    }

    public function testTell()
    {
        $stream = new Stream();
        $stream->write('foo');
        $this->assertEquals($stream->tell(), 3);
    }

    public function testSeek()
    {
        $stream = new Stream();
        $stream->write('foo');
        $stream->seek(1);
        $this->assertEquals($stream->read(1), 'o');

        $stream = $this->getMock(Stream::class, ['isSeekable']);
        $stream->method('isSeekable')->will($this->returnValue(false));
        try {
            $stream->seek(1);
            $this->fail('Must not here');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Could not seek in stream') {
                throw $e;
            }
        }
    }

    public function testGetContents()
    {
        $stream = new Stream();
        $stream->write('foo');
        $stream->rewind();
        $this->assertEquals($stream->getContents(), 'foo');

        $stream = $this->getMock(Stream::class, ['isReadable']);
        $stream->method('isReadable')->will($this->returnValue(false));
        try {
            $stream->getContents();
            $this->fail('Must not here');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Could not get contents of stream') {
                throw $e;
            }
        }
    }

    public function testRewind()
    {
        $stream = new Stream();
        $stream->write('foo bar');
        $this->assertEquals($stream->tell(), 7);
        $stream->rewind();
        $this->assertEquals($stream->tell(), 0);
        try {
            $stream->close();
            $stream->rewind();
            $this->fail('Must not reach here');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Could not rewind stream') {
                throw $e;
            }
        }
    }

    public function testWrite()
    {
        $stream = new Stream();
        $stream->write('foo bar');
        try {
            $stream->close();
            $stream->write('foo bar');
            $this->fail('Must not reach here');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Could not write to stream') {
                throw $e;
            }
        }
    }

    public function testRead()
    {
        $stream = new Stream();
        $stream->write('foo bar');
        $stream->rewind();
        $this->assertEquals($stream->read(3), 'foo');
        try {
            $stream->close();
            $stream->read(4);
            $this->fail('Must not reach here');
        } catch(BonoException $e) {
            if ($e->getMessage() !== 'Could not read from stream') {
                throw $e;
            }
        }
    }

    public function testGetMetadata()
    {
        $stream = new Stream();
        $this->assertEquals($stream->getMetadata('mode'), 'w+b');
    }
}