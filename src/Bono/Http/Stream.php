<?php

namespace Bono\Http;

use RuntimeException;
use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    protected static $modes = [
        'readable' => ['r', 'r+', 'w+', 'a+', 'x+', 'c+'],
        'writable' => ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'],
    ];

    protected $stream;

    protected $meta;

    protected $readable;

    protected $writable;

    protected $seekable;

    protected $size;

    public function __construct($stream = null)
    {
        $this->stream = $stream ?: fopen('php://temp', 'r+');

        $meta = $this->getMetadata();

        $this->seekable = $meta['seekable'];

        $this->writable = false;
        foreach (self::$modes['writable'] as $mode) {
            if (strpos($meta['mode'], $mode) === 0) {
                $this->writable = true;
                break;
            }
        }

        $this->readable = false;
        foreach (self::$modes['readable'] as $mode) {
            if (strpos($meta['mode'], $mode) === 0) {
                $this->readable = true;
                break;
            }
        }
    }

    public function __toString()
    {
        throw new \Exception('Unimplemented yet');
    }

    public function close()
    {
        throw new \Exception('Unimplemented yet');
    }

    public function detach()
    {
        throw new \Exception('Unimplemented yet');
    }

    public function getSize()
    {
        if (!$this->size) {
            $stats = fstat($this->stream);
            $this->size = isset($stats['size']) ? $stats['size'] : null;
        }

        return $this->size;
    }

    public function tell()
    {
        throw new \Exception('Unimplemented yet');
    }

    public function eof()
    {
        return feof($this->stream);
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \Exception('Unimplemented yet');
    }

    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new RuntimeException('Could not rewind stream');
        }
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function write($string)
    {
        if (!$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new RuntimeException('Could not write to stream');
        }

        // reset size so that it will be recalculated on next call to getSize()
        $this->size = null;

        return $written;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new RuntimeException('Could not read from stream');
        }

        return $data;
    }

    public function getContents()
    {
        throw new \Exception('Unimplemented yet');
    }

    public function getMetadata($key = null)
    {
        $this->meta = stream_get_meta_data($this->stream);
        if (is_null($key) === true) {
            return $this->meta;
        }

        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }
}
