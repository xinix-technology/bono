<?php

/**
 * Bono - PHP5 Web Framework
 *
 * MIT LICENSE
 *
 * Copyright (c) 2014 PT Sagara Xinix Solusitama
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Http
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Http;

/**
 * Request
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Http
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class Request extends \Slim\Http\Request
{
    protected $mediaTypeExtensions = array(
        'json' => 'application/json',
    );

    /**
     * [addMediaTypeExtensions description]
     *
     * @param [type] $mediaTypeExtensions [description]
     *
     * @return [type] [description]
     */
    public function addMediaTypeExtensions($mediaTypeExtensions)
    {
        $this->mediaTypeExtensions = array_merge($this->mediaTypeExtensions, $mediaTypeExtensions);
    }

    /**
     * [getPathInfo description]
     *
     * @return [type] [description]
     */
    public function getPathInfo()
    {
        return rtrim(parent::getPathInfo(), '/');
    }

    /**
     * [getResourceUri description]
     *
     * @return [type] [description]
     */
    public function getResourceUri()
    {
        $extensionLength = strlen($this->getExtension());
        $pathInfo = $this->getPathInfo();
        if ($extensionLength > 0) {
            return substr($pathInfo, 0, -($extensionLength + 1));
        }

        return $pathInfo;
    }

    /**
     * [getSegments description]
     *
     * @param integer $index [description]
     *
     * @return [type] [description]
     */
    public function getSegments($index = -1)
    {
        $segments = explode('/', $this->getResourceUri());
        if ($index < 0) {
            return $segments;
        } elseif (isset($segments[$index])) {
            return $segments[$index];
        }

        return null;
    }

    /**
     * [getExtension description]
     *
     * @return [type] [description]
     */
    public function getExtension()
    {
        return pathinfo($this->getPathInfo(), PATHINFO_EXTENSION);
    }

    /**
     * [getMediaType description]
     *
     * @return [type] [description]
     */
    public function getMediaType()
    {
        if ($ext = $this->getExtension()) {
            if (isset($this->mediaTypeExtensions[$ext])) {
                return $this->mediaTypeExtensions[$ext];
            }
        }

        return parent::getMediaType();
    }

    public function getBody()
    {
        if ($this->env['slim.input']) {
            return $this->env['slim.input'];
        } else {
            return $_POST;
        }
    }
}
