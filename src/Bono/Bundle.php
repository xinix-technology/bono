<?php

namespace Bono;

use InvalidArgumentException;
use FastRoute;
use FastRoute\Dispatcher;
use Bono\Http\Context;
use ROH\Util\Composition;
use ROH\Util\Collection as UtilCollection;
use Bono\Exception\ContextException;
use Bono\Exception\BonoException;
use Bono\Route\Dispatcher as BonoDispatcher;
use ROH\Util\Injector;

class Bundle extends UtilCollection
{
    protected $app;

    protected $middlewares = [];

    protected $bundles = [];

    protected $routes = [];

    protected $stack;

    protected $dispatcher;

    protected $composition;

    public function __construct(App $app, array $options = [])
    {
        parent::__construct($options);

        $this->app = $app;

        $this->configureMiddlewares();

        $this->configureRoutes();

        $this->configureBundles();
    }

    public function get($key, $default = null)
    {
        return $this[$key] ?: $default;
    }

    public function addBundle(array $bundle)
    {
        if (!isset($bundle['uri'])) {
            throw new BonoException('Bundle descriptor must have uri options');
        }

        if (!isset($bundle['handler'])) {
            throw new BonoException('Bundle descriptor must have handler options');
        }

        $this->bundles[] = $bundle;
        return $this;
    }

    public function dumpRoutes()
    {
        return $this->routes;
    }

    public function routeGet(array $route)
    {
        $route['methods'] = ['GET'];
        return $this->routeMap($route);
    }

    public function routePost(array $route)
    {
        $route['methods'] = ['POST'];
        return $this->routeMap($route);
    }

    public function routePut(array $route)
    {
        $route['methods'] = ['PUT'];
        return $this->routeMap($route);
    }

    public function routeDelete(array $route)
    {
        $route['methods'] = ['DELETE'];
        return $this->routeMap($route);
    }

    // implement this later if there is needs
    // public function routePatch(array $route)
    // {
    //     $route['methods'] = ['PATCH'];
    //     return $this->routeMap($route);
    // }

    // public function routeOptions(array $route)
    // {
    //     $route['methods'] = ['OPTIONS'];
    //     return $this->routeMap($route);
    // }

    public function routeAny(array $route)
    {
        $route['methods'] = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        return $this->routeMap($route);
    }

    public function routeMap(array $route)
    {
        if (!isset($route['uri'])) {
            throw new BonoException('Wrong route value registered');
        }

        if (!isset($route['methods'])) {
            $route['methods'] = [strtoupper(isset($route['method']) ? $route['method'] : 'get')];
            unset($route['method']);
        }

        $route['pattern'] = $route['uri'];
        $this->routes[] = $route;

        return $this;
    }

    public function getComposition()
    {
        if (null === $this->composition) {
            $this->composition = new Composition();
            $this->composition->setCore($this);
            foreach ($this->middlewares as $middleware) {
                $handler = Injector::getInstance()->resolve($middleware);
                $this->composition->compose($handler);
            }
        }
        return $this->composition;
    }

    public function dispatch(Context $context)
    {
        $path = $context->getPathname() ?: '/';

        // precompile to seal the middleware so we can add bundle from middleware
        $composition = $this->getComposition()->compile();

        $bundle = $this->getBundleFor($path);
        if (null === $bundle) {
            $routeInfo = $this->getDispatcher()->dispatch($context->getMethod(), $path);
            $context['route.info'] = $routeInfo;
        } else {
            $context['route.bundle'] = $bundle;
        }

        return $composition->apply($context);
    }

    public function __invoke(Context $context)
    {
        if (null !== ($routeInfo = $context['route.info'])) {
            switch ($routeInfo[0]) {
                case Dispatcher::FOUND:
                    $context->apply(function($context) use ($routeInfo) {
                        $context->setStatus(200);

                        foreach ($routeInfo[2] as $k => $v) {
                            $context[$k] = urldecode($v);
                        }

                        $state = $routeInfo[1]['handler']($context);

                        if ($state) {
                            $context->setState($state);
                        }
                    });
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $context->throwError(405);
                    break;
            }
        } elseif (null !== ($routeBundle = $context['route.bundle'])) {
            $context->shift($routeBundle['uri']);
            $routeBundle->dispatch($context);
            $context->unshift($routeBundle['uri']);
        } else {
            $context->throwError(404);
        }

    }

    public function getBundleFor($path)
    {
        $injector = Injector::getInstance();

        foreach ($this->bundles as $meta) {
            if (strpos($path, $meta['uri']) === 0) {
                $bundle = $injector->resolve($meta['handler'], [
                    'options' => [
                        'uri' => $meta['uri'],
                    ]
                ]);

                return $bundle;
            }
        }
    }

    public function addMiddleware($middleware)
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    public function __debugInfo()
    {
        $middlewares = [];
        $bundles = [];
        $routes = [];
        $attributes = [];

        foreach ($this->middlewares as $key => $middleware) {
            $middlewares[] = is_array($middleware) ? $middleware[0] : get_class($middleware);
        }

        foreach ($this->bundles as $key => $bundle) {
            $bundles[$bundle['uri']] = is_array($bundle['handler']) ? $bundle['handler'][0] :
                get_class($bundle['handler']);
        }

        $routes = array_map(function ($route) {
            return $route['pattern'];
        }, $this->routes);

        foreach ($this->attributes as $key => $attribute) {
            if ($key === 'middlewares' ||
                $key === 'bundles' ||
                $key === 'routes') {
                continue;
            }
            $attributes[$key] = $attribute;
        }

        return [
            'middlewares' => $middlewares,
            'bundles' => $bundles,
            'routes' => $routes,
            'attributes' => $attributes,
        ];
    }

    protected function configureMiddlewares()
    {
        if (isset($this['middlewares'])) {
            foreach ($this['middlewares'] as $middleware) {
                $this->addMiddleware($middleware);
            }
        }
    }

    protected function configureRoutes()
    {
        if (isset($this['routes'])) {
            foreach ($this['routes'] as $route) {
                $this->routeMap($route);
            }
        }
    }

    protected function configureBundles()
    {
        if (isset($this['bundles'])) {
            foreach ($this['bundles'] as $bundle) {
                $this->addBundle($bundle);
            }
        }
    }

    protected function getDispatcher()
    {
        if (null === $this->dispatcher) {
            $this->dispatcher = new BonoDispatcher($this->routes);
        }

        return $this->dispatcher;
    }
}
