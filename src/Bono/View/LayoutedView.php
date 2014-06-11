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
 * @category  PHP_Framework
 * @package   Bono
 * @author    Ganesha <reekoheek@gmail.com>
 * @copyright 2014 PT Sagara Xinix Solusitama
 * @license   https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version   0.10.0
 * @link      http://xinix.co.id/products/bono
 */
namespace Bono\View;

use Bono\App;

/**
 * Layouted View
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage View
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
class LayoutedView extends \Slim\View
{
    protected $layout = 'layout';

    protected $layoutView;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        parent::__construct();

        $this->layoutView = new \Slim\View();
    }

    /**
     * [fetch description]
     *
     * @param [type] $template [description]
     *
     * @return [type] [description]
     */
    public function fetch($template)
    {

        $app = App::getInstance();

        if (empty($template)) {
            return $this->data['body'];
        } else {
            if ($app->theme) {
                $template = $app->theme->resolve($template, $this) ?: $template;
            }

            $html = parent::fetch($template);

            $layoutTemplate = null;
            if ($this->layout) {
                if ($app->theme) {
                    $layoutTemplate = $app->theme->resolve($this->layout, $this->layoutView);
                } else {
                    $layoutTemplate = $this->layout;
                }
            }

            if ($layoutTemplate) {
                $this->layoutView->replace($this->all());
                $this->layoutView->set('body', $html);

                return $this->layoutView->render($layoutTemplate);
            } else {
                return $html;
            }
        }
    }

    /**
     * [setLayout description]
     *
     * @param [type] $layout [description]
     *
     * @return void
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }
}
