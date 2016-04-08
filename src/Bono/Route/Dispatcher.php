<?php
namespace Bono\Route;

use FastRoute\Dispatcher as FastRouteDispatcher;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\dataGenerator\GroupCountBased as DataGenerator;

class Dispatcher implements FastRouteDispatcher
{
    protected $routes;

    protected $staticRouteMap;

    protected $variableRouteData;

    public function __construct($routes)
    {
        $this->routes = $routes;

        $routeParser = new RouteParser();
        $dataGenerator = new DataGenerator();

        foreach ($routes as $route) {
            $route['parsedPattern'] = $routeParser->parse($route['pattern']);
            foreach ($route['methods'] as $method) {
                foreach ($route['parsedPattern'] as $parsed) {
                    $dataGenerator->addRoute($method, $parsed, $route);
                }
            }
        }

        list($this->staticRouteMap, $this->variableRouteData) = $dataGenerator->getData();
    }

    public function dispatch($httpMethod, $uri)
    {
        if (isset($this->staticRouteMap[$httpMethod][$uri])) {
            return [self::FOUND, $this->staticRouteMap[$httpMethod][$uri], []];
        } elseif ($httpMethod === 'HEAD' && isset($this->staticRouteMap['GET'][$uri])) {
            return [self::FOUND, $this->staticRouteMap['GET'][$uri], []];
        }

        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod])) {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        } elseif ($httpMethod === 'HEAD' && isset($varRouteData['GET'])) {
            $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }

        // Find allowed methods for this URI by matching against all other HTTP methods as well
        $allowedMethods = [];

        foreach ($this->staticRouteMap as $method => $uriMap) {
            if ($method !== $httpMethod && isset($uriMap[$uri])) {
                $allowedMethods[] = $method;
            }
        }

        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                $allowedMethods[] = $method;
            }
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        } else {
            return [self::NOT_FOUND];
        }
    }

    protected function dispatchVariableRoute($routeData, $uri)
    {
        foreach ($routeData as $data) {
            if (!preg_match($data['regex'], $uri, $matches)) {
                continue;
            }

            list($handler, $varNames) = $data['routeMap'][count($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }

    // protected function addStaticRoute($httpMethod, $routeData, $handler)
    // {
    //     $routeStr = $routeData[0];

    //     if (isset($this->staticRoutes[$httpMethod][$routeStr])) {
    //         throw new BadRouteException(sprintf(
    //             'Cannot register two routes matching "%s" for method "%s"',
    //             $routeStr,
    //             $httpMethod
    //         ));
    //     }

    //     if (isset($this->methodToRegexToRoutesMap[$httpMethod])) {
    //         foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
    //             if ($route->matches($routeStr)) {
    //                 throw new BadRouteException(sprintf(
    //                     'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
    //                     $routeStr,
    //                     $route->regex,
    //                     $httpMethod
    //                 ));
    //             }
    //         }
    //     }

    //     $this->staticRoutes[$httpMethod][$routeStr] = $handler;
    // }
}
