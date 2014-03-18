<?php

namespace Bono\Http;

class Request extends \Slim\Http\Request {
    protected $mediaTypeExtensions = array(
        'json' => 'application/json',
    );

    public function addMediaTypeExtensions($mediaTypeExtensions) {
        $this->mediaTypeExtensions = array_merge($this->mediaTypeExtensions, $mediaTypeExtensions);
    }

    public function getPathInfo() {
        return rtrim(parent::getPathInfo(), '/');
    }

    public function getResourceUri() {
        $extensionLength = strlen($this->getExtension());
        $pathInfo = $this->getPathInfo();
        if ($extensionLength > 0) {
            return substr($pathInfo, 0, -($extensionLength + 1));
        }
        return $pathInfo;
    }

    public function getSegments($index = -1) {
        $segments = explode('/', $this->getResourceUri());
        if ($index < 0) {
            return $segments;
        } elseif (isset($segments[$index])) {
            return $segments[$index];
        }
        return NULL;
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
}
