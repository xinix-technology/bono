<?php
namespace Bono\Http;

use Bono\Exception\BonoException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    public $file;

    protected $name;

    protected $type;

    protected $size;

    protected $error = UPLOAD_ERR_OK;

    protected $sapi = false;

    protected $stream;

    protected $moved = false;

    public static function byEnvironment(array $env, $cli = false)
    {
        return static::parseUploadedFiles($env);
    }

    private static function parseUploadedFiles(array $uploadedFiles)
    {
        $parsed = [];
        foreach ($uploadedFiles as $field => $uploadedFile) {
            if (!isset($uploadedFile['error'])) {
                if (is_array($uploadedFile)) {
                    $parsed[$field] = static::parseUploadedFiles($uploadedFile);
                }
                continue;
            }
            $parsed[$field] = [];
            if (!is_array($uploadedFile['error'])) {
                $parsed[$field] = new static(
                    $uploadedFile['tmp_name'],
                    isset($uploadedFile['name']) ? $uploadedFile['name'] : null,
                    isset($uploadedFile['type']) ? $uploadedFile['type'] : null,
                    isset($uploadedFile['size']) ? $uploadedFile['size'] : null,
                    $uploadedFile['error'],
                    true
                );
            } else {
                $subArray = [];
                foreach ($uploadedFile['error'] as $fileIdx => $error) {
                    // normalise subarray and re-parse to move the input's keyname up a level
                    $subArray[$fileIdx]['name'] = $uploadedFile['name'][$fileIdx];
                    $subArray[$fileIdx]['type'] = $uploadedFile['type'][$fileIdx];
                    $subArray[$fileIdx]['tmp_name'] = $uploadedFile['tmp_name'][$fileIdx];
                    $subArray[$fileIdx]['error'] = $uploadedFile['error'][$fileIdx];
                    $subArray[$fileIdx]['size'] = $uploadedFile['size'][$fileIdx];
                    $parsed[$field] = static::parseUploadedFiles($subArray);
                }
            }
        }
        return $parsed;
    }

    public function __construct($file, $name = null, $type = null, $size = null, $error = UPLOAD_ERR_OK, $sapi = false)
    {
        $this->file = $file;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    public function getStream()
    {
        if ($this->moved) {
            throw new BonoException(sprintf('Uploaded file %1s has already been moved', $this->name));
        }
        if ($this->stream === null) {
            $this->stream = new Stream(fopen($this->file, 'r'));
        }
        return $this->stream;
    }

    public function moveTo($targetPath)
    {
        if ($this->moved) {
            throw new BonoException('Uploaded file already moved');
        }
        if (!is_writable(dirname($targetPath))) {
            throw new BonoException('Upload target path is not writable');
        }
        $targetIsStream = strpos($targetPath, '://') > 0;
        if ($targetIsStream) {
            if (!copy($this->file, $targetPath)) {
                throw new BonoException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
            if (!unlink($this->file)) {
                throw new BonoException(sprintf('Error removing uploaded file %1s', $this->name));
            }
        } elseif ($this->sapi) {
            if (!is_uploaded_file($this->file)) {
                throw new BonoException(sprintf('%1s is not a valid uploaded file', $this->file));
            }
            if (!move_uploaded_file($this->file, $targetPath)) {
                throw new BonoException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        } else {
            if (!rename($this->file, $targetPath)) {
                throw new BonoException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
            }
        }
        $this->moved = true;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getClientFilename()
    {
        return $this->name;
    }

    public function getClientMediaType()
    {
        return $this->type;
    }

    public function getSize()
    {
        return $this->size;
    }
}