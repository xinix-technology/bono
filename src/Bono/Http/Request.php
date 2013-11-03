<?php

namespace Bono\Http;

class Request extends \Slim\Http\Request {
    protected $mediaTypeExtensions = array(
        'json' => 'application/json',
    );

    public function addMediaTypeExtensions($mediaTypeExtensions) {
        $this->mediaTypeExtensions = array_merge($this->mediaTypeExtensions, $mediaTypeExtensions);
    }

    public function getResourceUri() {
        $extensionLength = strlen($this->getExtension());
        $pathInfo = $this->getPathInfo();
        if ($extensionLength > 0) {
            return substr($pathInfo, 0, -($extensionLength + 1));
        }
        return $pathInfo;
    }

    public function getExtension() {
        return pathinfo($this->getPathInfo(), PATHINFO_EXTENSION);
    }

    public function getMediaType() {
        if ($ext = $this->getExtension()) {
            if (isset($this->mediaTypeExtensions[$ext])) {
                return $this->mediaTypeExtensions[$ext];
            }
        }

        return parent::getMediaType();
    }

    // public function getAccepts() {
    //     $results = array();

    //     $accepts = explode(',', $this->env['HTTP_ACCEPT']);
    //     foreach ($accepts as $accept) {
    //         $accept = explode(';', $accept);
    //         $result = array(
    //             'contentType' => $accept[0],
    //             'q' => 1.0
    //         );
    //         if (isset($accept[1])) {
    //             $accepted = explode('=', $accept[1]);
    //             $result['q'] = (double) $accepted[1];
    //         }
    //         $results[] = $result;
    //     }
    //     return $results;
    // }

    // public function post($key = null) {
    //     if (!isset($this->env['slim.input'])) {
    //         throw new \RuntimeException('Missing slim.input in environment variables');
    //     }

    //     if ($this->getMediaType() == 'application/json') {
    //         return json_decode($this->env['slim.input'], 1);
    //     }

    //     return parent::post();
    // }
}