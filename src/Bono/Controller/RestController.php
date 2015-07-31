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
 * @subpackage Controller
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
namespace Bono\Controller;

/**
 * RestController is base class for REST-enabled routing controller.
 *
 * The class cannot be used directly since it is abstract class. Developer who
 * wants to use RestController should extend the RestController and implements
 * the <b>search, create, read, update, delete</b> methods.
 *
 * @category   PHP_Framework
 * @package    Bono
 * @subpackage Controller
 * @author     Ganesha <reekoheek@gmail.com>
 * @copyright  2014 PT Sagara Xinix Solusitama
 * @license    https://raw.github.com/xinix-technology/bono/master/LICENSE MIT
 * @version    0.10.0
 * @link       http://xinix.co.id/products/bono
 */
abstract class RestController extends Controller
{
    /**
     * Constructor
     * @param Bono\App $app
     * @param string $baseUri
     */
    public function __construct($app, $baseUri)
    {
        parent::__construct($app, $baseUri);

        $controller = $this;

        $app->filter('controller.schema', function () use ($controller) {
            return $controller->schema() ?: array();
        });
    }

    /**
     * Map routes to available method
     *
     * @return [type] [description]
     */
    public function mapRoute()
    {
        $this->map('/null/create', 'create')->via('GET', 'POST');
        $this->map('/:id/read', 'read')->via('GET');
        $this->map('/:id/update', 'update')->via('GET', 'POST');
        $this->map('/:id/delete', 'delete')->via('GET', 'POST');

        $this->map('/', 'search')->via('GET');
        $this->map('/', 'create')->via('POST');
        $this->map('/:id', 'read')->via('GET');
        $this->map('/:id', 'update')->via('PUT');
        $this->map('/:id', 'delete')->via('DELETE');

    }

    /**
     * Search method map to / group route
     *
     * @return [type] [description]
     */
    abstract public function search();

    /**
     * Create method map to /null/create group route
     *
     * @return [type] [description]
     */
    abstract public function create();

    /**
     * Read method map to /:id group route
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    abstract public function read($id);

    /**
     * Update method map to /:id/update group route
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    abstract public function update($id);

    /**
     * Delete method map to /:id/delete group route
     *
     * @param [type] $id [description]
     *
     * @return [type] [description]
     */
    abstract public function delete($id);

    /**
     * Getter and setter for schema
     * @param  array $schema  Schema to set if this argument is defined
     * @return mixed
     */
    abstract public function schema($schema = null);
}
