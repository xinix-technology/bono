<?php

namespace Bono\Controller;

use \ROH\Util\Inflector;

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
        $clazz = $this->clazz = Inflector::classify(end($exploded));

        // DEPRECATED reekoheek: remove inside dependency of _controller to view
        // $this->data['_controller'] = $controller = $this;

        $controller = $this;

        $response = $this->response;

        $app->filter('controller.name', function() use ($clazz) {
            return $clazz;
        });

        $app->filter('controller.url', function($uri) use ($controller) {
            return URL::site($controller->getBaseUri().$uri);
        });

        $app->hook('bono.controller.before', function($options) use ($app, $controller, $response) {
            if (is_readable($app->config('templates.path') . $controller->getBaseUri() .'/' . $options['method'] . '.php')) {
                $response->template($controller->getBaseUri().'/'.$options['method']);
            } else {
                $response->template('shared/'.$options['method']);
            }
        });

        $app->hook('bono.controller.after', function($options) use ($app, $controller, $response) {

            $response->set($controller->getData());
        });

        $this->mapRoute();
    }

    public function getData(){
        return $this->data;
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