<?php

namespace Bono\Http;

use Bono\Exception\BonoException;
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
        $this->rewind();
        $result = [];
        while (!$this->eof()) {
            $chunk = $this->read(1024);
            $result[] = $chunk;
        }
        return implode('', $result);
    }

    public function close()
    {
        if (null !== $this->stream) {
            fclose($this->stream);
            $this->stream = null;
            $this->seekable = false;
            $this->writable = false;
            $this->readable = false;
        }
    }

    public function isOpen()
    {
        return null !== $this->stream;
    }

    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
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
        return ftell($this->stream);
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
        // Note that fseek returns 0 on success!
        if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new BonoException('Could not seek in stream');
        }
    }


    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new BonoException('Could not rewind stream');
        }
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function write($string)
    {
        if (!$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new BonoException('Could not write to stream');
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
            throw new BonoException('Could not read from stream');
        }

        return $data;
    }

    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
            throw new BonoException('Could not get contents of stream');
        }
        return $contents;
    }

    public function getMetadata($key = null)
    {
        $this->meta = stream_get_meta_data($this->stream);
        if (null === $key) {
            return $this->meta;
        }

        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }
}
