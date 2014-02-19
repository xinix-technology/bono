<?php

namespace Bono\Middleware;

use \Bono\Helper\URL;

class AuthMiddleware extends \Slim\Middleware {
    public function inArray($string, $array = array()) {
        if (empty($array) || empty($string)) return false;
        foreach ($array as $key => $value) {
            if (fnmatch($key, $string)) {
                return true;
            }
        }
        return false;
    }

    public function call() {
        $config = $this->app->config('auth');

        $pathInfo = $this->app->request->getPathInfo();
        $app = $this->app;
        $request = $this->app->request;
        $response = $this->app->response;

        $this->app->get('/login', function() use($app, $response) {
            $config = $this->app->config('auth');

            $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base('auth');

            $this->app->response->redirect($config['urlServiceProvider'].'login?@continue='.urlencode($selfUrl));
        });

        $this->app->get('/auth', function() use($app, $response) {
            $get = $app->request->get();
            $appId = $this->app->config('appId');
            $secret = $this->app->config('secret');

            $service = array(
                'ticket' => $get['@ticket'],
                'appId'  => $appId,
                'secret'  => $secret
            );

            $this->app->auth->authenticate($service);

            $this->app->response->redirect('/');
        });

        $this->app->get('/logout', function() use($app, $response) {
            $config = $this->app->config('auth');

            $this->app->auth->deauthenticate();
            $this->app->auth->logout();

            $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base();

            $this->app->response->redirect($config['urlServiceProvider'].'logout?@continue='.urlencode($selfUrl));
        });

        $allow = false;

        if ($this->inArray($pathInfo, $config['allow']) && ! $this->inArray($pathInfo, @$config['restricted'])) {
            $allow = true;
        }

        if (! $allow && ! $this->app->auth->check()) {
            // Redirect to login
            $selfUrl = 'http://'.$_SERVER['HTTP_HOST'].URL::base('auth');
            $this->app->response->redirect($config['urlServiceProvider'].'login?@continue='.urlencode($selfUrl));
        } else {
            $this->next->call();
        }

    }
}
