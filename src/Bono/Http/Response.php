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
 * Response
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
class Response extends \Slim\Http\Response
{

    /**
     * [$template description]
     * @var string
     */
    protected $template = '';

    /**
     * Response Data
     *
     * @var array
     */
    protected $data = array();

    protected $messagesStatus = array (
        208 => '208 not availabe'
    );

    public function __construct($body = '', $status = 200, $headers = array())
    {
        parent::__construct($body, $status, $headers);

        foreach ($this->messagesStatus as $key => $value) {
            $this->setMessageStatus($key, $value);
        }
    }

    public function setMessageStatus($code, $message)
    {
        static::$messages[$code] = $message;
    }

    /**
     * [set description]
     *
     * @param [type] $key   [description]
     * @param [type] $value [description]
     *
     * @return [type] [description]
     *
     * @deprecated
     *
     */
    public function set($key, $value)
    {
        trigger_error(__METHOD__.' is deprecated.', E_USER_DEPRECATED);

        return $this->data($key, $value);
    }

    /**
     * [reset description]
     * @return [type] [description]
     *
     * @deprecated
     *
     */
    public function reset()
    {
        trigger_error(__METHOD__.' is deprecated.', E_USER_DEPRECATED);

        return $this->data(null);
    }

    /**
     * [get description]
     *
     * @param [type] $key [description]
     *
     * @return [type] [description]
     *
     * @deprecated
     *
     */
    public function get($key = null)
    {
        trigger_error(__METHOD__.' is deprecated.', E_USER_DEPRECATED);

        if (0 === func_num_args()) {
            return $this->data();
        } else {
            return $this->data($key);
        }
    }

    /**
     * [template description]
     *
     * @param [type] $template [description]
     *
     * @return [type] [description]
     */
    public function template($template = null)
    {
        if (is_null($template)) {
            return $this->template;
        } else {
            $this->template = $template;
        }
    }

    /**
     * [redirect description]
     *
     * @param string  $url    [description]
     * @param integer $status [description]
     *
     * @return [type] [description]
     */
    public function redirect($url = ':self', $status = 302)
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (isset($scheme)) {
            return parent::redirect($url, $status);
        }
        if ($url === ':self') {
            $app = \Slim\Slim::getInstance();
            $url = $app->request->getResourceUri();
        }
        $url = \Bono\Helper\URL::site($url);

        return parent::redirect($url, $status);
    }

    /**
     * [data description]
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function data($key = null, $value = null) {
        switch (func_num_args()) {
            case 0:
                return $this->data;
            case 1:
                if (is_array($key)) {
                    $this->data = array_merge($this->data, $key);
                    return $this;
                } elseif (is_null($key)) {
                    $this->data = array();
                    return $this;
                } elseif (isset($this->data[$key])) {
                    return $this->data[$key];
                } else {
                    return;
                }
            case 2:
                if (is_null($value)) {
                    unset($this->data[$key]);
                } else {
                    $this->data[$key] = $value;
                }
                return $this;

        }
    }

}
