<?php

namespace Bono;

use ROH\Util\Injector;
use ROH\Util\Options;
use Bono\Http\Context;
use Bono\Bundle;

abstract class Executor extends Options
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Injector
     */
    protected $injector;

    protected $bundleDef;

    public function __construct(Injector $injector, $bundleDef, $options = [])
    {
        parent::__construct([
            'date.timezone' => 'UTC',
            'route.dispatcher' => 'simple',
            'process.cli' => PHP_SAPI === 'cli',
        ]);

        $this->merge($options);

        $injector->singleton(Executor::class, $this);

        $this->injector = $injector;
        $this->bundleDef = $bundleDef;

        date_default_timezone_set($this['date.timezone']);
    }

    /**
     * Getter bundle
     *
     * @return Bundle
     */
    public function getBundle()
    {
        return $this->injector->resolve($this->bundleDef);
    }

    /**
     * Dispatch context
     */
    public function dispatch(Context $ctx)
    {
        $this->getBundle()->dispatch($ctx);
    }
}
