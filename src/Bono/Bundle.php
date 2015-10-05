<?php

namespace Bono;

use InvalidArgumentException;
use FastRoute;
use FastRoute\Dispatcher;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Bono\Http\Response;
use ROH\Util\Thing;
use ROH\Util\Collection;

class Bundle extends Collection
{
    protected $middlewares = [];

    protected $bundles = [];

    protected $routes = [];

    protected $stack;

    protected $dispatcher;

    public function __construct($options = array())
    {
        parent::__construct($options);

        $this->configureMiddlewares();

        $this->configureRoutes();

        $this->configureBundles();
    }

    public function configureMiddlewares()
    {
        if (isset($this['middlewares'])) {
            foreach ($this['middlewares'] as $middleware) {
                $this->addMiddleware($middleware);
            }
        }
    }

    public function configureRoutes()
    {
        if (isset($this['routes'])) {
            foreach ($this['routes'] as $route) {
                $method = isset($route['method']) ? $route['method'] : 'get';

                call_user_func(array($this, 'routeMap'), [strtoupper($method)], $route['uri'], $route['handler']);
            }
        }
    }

    public function configureBundles()
    {
        if (isset($this['bundles'])) {
            foreach ($this['bundles'] as $bundle) {
                $this->addBundle($bundle);
            }
        }
    }

    public function getOptions()
    {
        return $this->attributes;
    }

    public function getOption($key, $def = null)
    {
        return isset($this[$key]) ? $this[$key] : $def;
    }

    public function setOption($key, $value)
    {
        $this[$key] = $value;
    }

    public function getDispatcher()
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

    public function addBundle($bundle)
    {
        if (!($bundle instanceof Thing)) {
            $bundle = new Thing($bundle);
        }

        // TODO should we incorporate uri to config?
        // if (!$bundle['handler']) {
        //     if ($bundle['config']) {
        //         $bundle['config']['uri'] = $bundle['uri'];
        //     } else {
        //         $bundle['config'] = ['uri' => $bundle['uri']];
        //     }
        // }

        $this->bundles[] = $bundle;
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
    }

    public function dispatch(ServerRequestInterface $request)
    {
        $request['bundle'] = $this;

        if (is_null($this->stack)) {
            $this->stack = [$this];
            if ($this->middlewares) {
                $len = count($this->middlewares);
                for ($i = $len - 1; $i >= 0; $i--) {
                    $next = $this->stack[0];

                    $handler = $this->middlewares[$i]->getHandler();

                    array_unshift($this->stack, function (
                        ServerRequestInterface $request
                    ) use (
                        $next,
                        $handler
                    ) {
                        return call_user_func($handler, $request, $next);
                    });
                }
            }
        }

        $path = '/' . ltrim($request->getUri()->getPathname(), '/');

        $bundle = $this->getBundleFor($path);
        if (is_null($bundle)) {
            $routeInfo = $this->getDispatcher()->dispatch($request->getMethod(), $path);
            $request['routeInfo'] = $routeInfo;
        } else {
            $request['routeBundle'] = $bundle;
        }

        return $this->stack[0]($request);
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $routeInfo = $request['routeInfo'];

        if ($routeInfo) {
            switch ($routeInfo[0]) {
                case Dispatcher::FOUND:
                    foreach ($routeInfo[2] as $k => $v) {
                        $request[$k] = urldecode($v);
                    }

                    try {
                        $response = $routeInfo[1]($request);

                        if (!($response instanceof ResponseInterface)) {
                            $response = new Response(200, null, $response);
                        }
                    } catch (\Exception $e) {
                        $response = Response::error(500, $e);
                    }
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $response = Response::error(405);
                    break;
                default:
                    $response = Response::error(404);
            }

            // set response request
            $response['bundle'] = $this;
            $response['request'] = $request;
        } else {
            $routeBundle = $request['routeBundle'];
            $request = $request->shift($routeBundle['uri']);
            $response = $routeBundle->getHandler()->dispatch($request);
        }

        return $response;
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
}
