<?php

namespace Bono\Provider;

class ControllerProvider {

    public function initialize($app) {

        $name = explode('/', substr($app->request->getResourceUri(), 1), 2)[0];

        $config = $app->config('bono.controller');
        $mapping = $config['mapping'];
        $defaultController = $config['default'];

        if (empty($defaultController)) {
            $defaultController = '\\Bono\\Controller\\RestController';
        }

        if (array_key_exists($name, $mapping)) {
            if (empty($mapping[$name])) {
                $FullClassName = $defaultController;
            } else {
                $FullClassName = $mapping[$name];
            }
        } elseif ($config['auto']) {
            $FullClassName = $app->getNS('controllers\\'.$name);
        }

        if(class_exists($FullClassName)) {
            $o = new $FullClassName($app, $name);
            if (method_exists($o, 'register')) {
                $o->register();
            }
        }
    }

}