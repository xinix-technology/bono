<?php

namespace Bono\Controller;

use \Reekoheek\Util\Inflector;

abstract class Controller implements IController {

    public $clazz;

    protected $app;
    protected $request;
    protected $response;

    protected $baseUri;

    protected $data = array();

    public function __construct($app, $baseUri) {
        $this->app = $app;
        $this->request = $app->request;
        $this->response = $app->response;

        $this->baseUri = $baseUri;
        $exploded = explode('/', $baseUri);
        $this->clazz = Inflector::classify(end($exploded));

        // DEPRECATED reekoheek: remove inside dependency of _controller to view
        // $this->data['_controller'] = $controller = $this;

        $controller = $this;

        $app->hook('bono.controller.before', function($options) use ($app, $controller) {
            if (is_readable($app->config('templates.path') . $controller->getBaseUri() .'/' . $options['method'] . '.php')) {
                $controller->response->template($controller->getBaseUri().'/'.$options['method']);
            } else {
                $controller->response->template('shared/'.$options['method']);
            }
        });

        $app->hook('bono.controller.after', function($options) use ($app, $controller) {
            $controller->response->set($controller->data);
        });

        $this->mapRoute();
    }

    public function set($key, $value) {
        $this->data[$key] = $value;
        return $this;
    }

    public function delegate($method, $args = array()) {
        $options = array(
            'method' => $method,
            'controller' => $this,
        );
        $this->app->applyHook('bono.controller.before', $options, 1);

        $argCount = count($args);
        switch($argCount) {
            case 0:
                $this->$method();
                break;
            case 1:
                $this->$method($args[0]);
                break;
            case 2:
                $this->$method($args[0], $args[1]);
                break;
            case 3:
                $this->$method($args[0], $args[1], $args[2]);
                break;
            case 4:
                $this->$method($args[0], $args[1], $args[2], $args[3]);
                break;
            case 5:
                $this->$method($args[0], $args[1], $args[2], $args[3], $args[4]);
                break;
            default:
                call_user_func_array(array($this, $method), $args);

        }
        $this->app->applyHook('bono.controller.after', $options, 20);
    }


    public function map($uri, $method) {
        if ($uri === '/') {
            $uri = '';
        }

        $controller = $this;

        return $this->app->map($this->baseUri.$uri, function() use ($controller, $method) {
            $controller->delegate($method, func_get_args());
        });
    }

    public function getBaseUri() {
        return $this->baseUri;
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

    abstract public function mapRoute();

}