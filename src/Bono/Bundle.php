<?php

namespace Bono;

use InvalidArgumentException;
use FastRoute;
use FastRoute\Dispatcher;
use Bono\Http\Context;
use ROH\Util\Thing;
use ROH\Util\Collection as UtilCollection;
use Bono\Exception\ContextException;
use Bono\Exception\BonoException;

class Bundle extends UtilCollection
{
    protected $middlewares = [];

    protected $bundles = [];

    protected $routes = [];

    protected $stack;

    protected $dispatcher;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

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
        $this->bundles[] = new Thing($bundle);
        return $this;
    }

    public function dumpRoutes()
    {
        return $this->routes;
    }

    public function routeGet($pattern, $handler)
    {
        return $this->routeMap(['GET'], $pattern, $handler);
    }

    public function routePost($pattern, $handler)
    {
        return $this->routeMap(['POST'], $pattern, $handler);
    }

    public function routePut($pattern, $handler)
    {
        return $this->routeMap(['PUT'], $pattern, $handler);
    }

    public function routeDelete($pattern, $handler)
    {
        return $this->routeMap(['DELETE'], $pattern, $handler);
    }

    public function routePatch($pattern, $handler)
    {
        return $this->routeMap(['PATCH'], $pattern, $handler);
    }

    public function routeOptions($pattern, $handler)
    {
        return $this->routeMap(['OPTIONS'], $pattern, $handler);
    }

    public function routeAny($pattern, $handler)
    {
        return $this->routeMap(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'], $pattern, $handler);
    }

    public function routeMap(array $methods, $pattern, $handler)
    {
        $this->routes[] = [
            'methods' => $methods,
            'pattern' => $pattern,
            'handler' => $handler,
        ];

        return $this;
    }

    public function dispatch(Context $context)
    {
        $context->withBundle($this);

        if (is_null($this->stack)) {
            $this->stack = [$this];
            if ($this->middlewares) {
                $len = count($this->middlewares);
                for ($i = $len - 1; $i >= 0; $i--) {
                    $next = $this->stack[0];

                    $handler = $this->middlewares[$i]->getHandler();

                    array_unshift($this->stack, function (
                        Context $context
                    ) use (
                        $next,
                        $handler
                    ) {
                        return call_user_func($handler, $context, $next);
                    });
                }
            }
        }

        $path = $context->getPathname() ?: '/';

        $bundle = $this->getBundleFor($path);
        if (is_null($bundle)) {
            $routeInfo = $this->getDispatcher()->dispatch($context->getMethod(), $path);
            $context['routeInfo'] = $routeInfo;
        } else {
            $context['routeBundle'] = $bundle;
        }

        return $this->stack[0]($context);
    }

    public function __invoke(Context $context)
    {
        $routeInfo = $context['routeInfo'];
        if (isset($routeInfo)) {
            switch ($routeInfo[0]) {
                case Dispatcher::FOUND:
                    $context->withStatus(200);

                    foreach ($routeInfo[2] as $k => $v) {
                        $context[$k] = urldecode($v);
                    }

                    $result = $routeInfo[1]($context);

                    if ($result) {
                        $context->withState($result);
                    }
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $context->throwError(405);
                    break;
            }
        } else {
            $routeBundle = $context['routeBundle'];
            if (isset($routeBundle)) {
                $context->shift($routeBundle['uri']);
                $routeBundle->getHandler()->dispatch($context);
                $context->unshift($routeBundle['uri']);
            }
        }
    }

    public function getBundleFor($path)
    {
        foreach ($this->bundles as $bundle) {
            if (strpos($path, $bundle['uri']) === 0) {
                return $bundle;
            }
        }
    }

    public function addMiddleware($middleware)
    {
        if (!($middleware instanceof Thing)) {
            $middleware = new Thing($middleware);
        }

        $this->middlewares[] = $middleware;
    }

    public function __debugInfo()
    {
        $middlewares = [];
        $bundles = [];
        $routes = [];
        $attributes = [];

        foreach ($this->middlewares as $key => $middleware) {
            $middlewares[] = $middleware['class'] ?: get_class($middleware->getHandler());
        }

        foreach ($this->bundles as $key => $bundle) {
            $bundles[$bundle['uri']] = $bundle['class'];
        }

        foreach ($this->routes as $key => $route) {
            $routes[$route['pattern']] = true;
        }

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
                if (!isset($route['uri'])) {
                    throw new BonoException('Wrong route value registered');
                }
                $method = isset($route['method']) ? $route['method'] : 'get';

                call_user_func(array($this, 'routeMap'), [strtoupper($method)], $route['uri'], $route['handler']);
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
        if (is_null($this->dispatcher)) {
            $app = App::getInstance();

            $dispatcherCallback = function (FastRoute\RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route['methods'], $route['pattern'], $route['handler']);
                }
            };

            switch ($app['route.dispatcher']) {
                case 'simple':
                    $this->dispatcher = FastRoute\simpleDispatcher($dispatcherCallback);
                    break;
                default:
                    throw new \Exception('Unimplemented yet');
            }
            // $dispatcherFactory;
            // }, [
            //     'routeParser' => 'FastRoute\\RouteParser\\Std',
            //     'dataGenerator' => 'FastRoute\\DataGenerator\\GroupCountBased',
            //     'dispatcher' => 'FastRoute\\Dispatcher\\GroupCountBased',
            // ]);
        }

        return $this->dispatcher;
    }
}
