<?php

namespace Bono\Http;

class Request extends \Slim\Http\Request {

    protected $MIME_TYPE = array(
        'json' => array(
            'extension' => array( 'json' ),
            'contentType' => array( 'application/json' ),
        ),
        'html' => array(
            'extension' => array( 'html' ),
            'contentType' => array( 'text/html' ),
        ),
    );

    public function getMimeClass() {
        $mime = $this->getMime();
        return $mime[0];
    }

    public function getMime($mime = '') {
        if ($mime AND is_array($mime)) {
            return $mime;
        }

        if ($mime AND is_string($mime)) {
            $extension = $mime;
        } else {
            $extension = $this->getExtension();
        }
        if (!empty($extension)) {
            foreach ($this->MIME_TYPE as $mimeClass => $mimeConfig) {
                if (in_array($extension, $mimeConfig['extension'])) {
                    return array( $mimeClass, $mimeConfig['contentType'][0]);
                }
            }
        }

        $accepts = array_map(function($accept) {
            return $accept['contentType'];
        }, $this->getAccepts());

        foreach ($this->MIME_TYPE as $mimeClass => $mimeConfig) {
            foreach ($mimeConfig['contentType'] as $contentType) {
                if (in_array($contentType, $accepts)) {
                    return array( $mimeClass, $contentType);
                }
            }
        }
    }

    public function getResourceUri() {
        $extensionLength = strlen($this->getExtension());
        $pathInfo = $this->getPathInfo();
        if ($extensionLength > 0) {
            return substr($pathInfo, 0, -($extensionLength+1));
        }
        return $pathInfo;
    }

    public function getExtension() {
        return pathinfo($this->getPathInfo(), PATHINFO_EXTENSION);
    }

    public function getAccepts() {
        $results = array();

        $accepts = explode(',', $this->env['HTTP_ACCEPT']);
        foreach ($accepts as $accept) {
            $accept = explode(';', $accept);
            $result = array(
                'contentType' => $accept[0],
                'q' => 1.0
            );
            if (isset($accept[1])) {
                $acceptSplitted = explode('=', $accept[1]);
                $result['q'] = (double) $acceptSplitted[1];
            }
            $results[] = $result;
        }
        return $results;
    }

    public function post($key = null) {
        if (!isset($this->env['slim.input'])) {
            throw new \RuntimeException('Missing slim.input in environment variables');
        }

        if ($this->getMediaType() == 'application/json') {
            return json_decode($this->env['slim.input'], 1);
        }

        return parent::post();
    }
}