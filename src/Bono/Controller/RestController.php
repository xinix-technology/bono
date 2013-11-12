<?php

namespace Bono\Controller;

use \Reekoheek\Util\Inflector;

abstract class RestController {
    public $clazz;

    protected $app;
    protected $request;
    protected $response;

    protected $baseUri;
    protected $activeRoute;
    protected $data = array();

    public function __construct($app, $baseUri) {
        $exploded = explode('/', $baseUri);
        $this->clazz = Inflector::classify(end($exploded));

        $this->app = $app;
        $this->request = $app->request;
        $this->response = $app->response;

        $this->baseUri = $baseUri;

        $this->data = array(
            '_controller' => $this,
        );

        $controller = $this;

        $app->group($baseUri, function() use ($app, $controller) {
            $app->map('/null/create', array($controller, 'delegate'))->name('create')->via('GET', 'POST');
            $app->map('/:id/update', array($controller, 'delegate'))->name('update')->via('GET', 'POST');
            $app->map('/:id/delete', array($controller, 'delegate'))->name('delete')->via('GET', 'POST');

            $app->get('/', array($controller, 'delegate'))->name('search');
            $app->post('/', array($controller, 'delegate'))->name('create');
            $app->get('/:id', array($controller, 'delegate'))->name('read');
            $app->put('/', array($controller, 'delegate'))->name('update');
            $app->delete('/', array($controller, 'delegate'))->name('delete');
        });
    }

    public function getBaseUri() {
        return $this->baseUri;
    }

    public function delegate() {
        $this->activeRoute = $this->app->router()->getCurrentRoute();

        $this->preAction();
        call_user_func_array(array($this, $this->activeRoute->getName()), func_get_args());
        $this->postAction();
    }

    public function preAction() {
        $method = $this->app->router()->getCurrentRoute()->getName();

        if (is_readable($this->app->config('templates.path') . $this->baseUri .'/' . $method . '.php')) {
            $this->response->template($this->baseUri.'/'.$method);
        } else {
            $this->response->template('shared/'.$method);
        }

        $post = $this->request->post();
        if (!empty($post)) {
            $this->data['entry'] = $post;
        }
    }

    public function postAction() {
        $this->response->set($this->data);
    }

    public function redirect($url, $status = 302) {
        $this->app->redirect($url, $status);
    }

    public function flash($key, $value) {
        $this->app->flash($key, $value);
    }

    public function flashNow($key, $value) {
        $this->app->flashNow($key, $value);
    }

    public function flashKeep() {
        $this->app->flashKeep();
    }

    abstract function search();
    abstract function create();
    abstract function read($id);
    abstract function update($id);
    abstract function delete($id);

}