<?php

namespace Bono\Bundle;

use Bono\Bundle;
use Bono\Http\Context;
use LogicException;
use Bono\App;

abstract class Rest extends Bundle
{
    public function __construct(App $app, array $options = [])
    {
        $this->addMiddleware(function (Context $context, $next) {
            $context->depends('@bodyParser');

            $next($context);
        });

        parent::__construct($app, $options);

        $this->routeMap([ 'methods' => ['GET'], 'uri' => '/', 'handler' => [$this, 'search'] ]);
        $this->routeMap([ 'methods' => ['POST'], 'uri' => '/', 'handler' => [$this, 'create'] ]);
        $this->routeMap([ 'methods' => ['GET'], 'uri' => '/{id}', 'handler' => [$this, 'read'] ]);
        $this->routeMap([ 'methods' => ['PUT'], 'uri' => '/{id}', 'handler' => [$this, 'update'] ]);
        $this->routeMap([ 'methods' => ['DELETE'], 'uri' => '/{id}', 'handler' => [$this, 'delete'] ]);

        $this->routeMap([ 'methods' => ['GET', 'POST'], 'uri' => '/null/create', 'handler' => [$this, 'create'] ]);
        $this->routeMap([ 'methods' => ['GET'], 'uri' => '/{id}/read', 'handler' => [$this, 'read'] ]);
        $this->routeMap([ 'methods' => ['GET', 'PUT'], 'uri' => '/{id}/update', 'handler' => [$this, 'update'] ]);
        $this->routeMap([ 'methods' => ['GET', 'DELETE'], 'uri' => '/{id}/delete', 'handler' => [$this, 'delete'] ]);
    }

    abstract public function search(Context $context);
    abstract public function create(Context $context);
    abstract public function read(Context $context);
    abstract public function update(Context $context);
    abstract public function delete(Context $context);
    abstract public function getSchema(Context $context);
}
