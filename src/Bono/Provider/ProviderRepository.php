<?php

namespace Bono\Provider;

class ProviderRepository {

    protected $app;
    protected $providers;

    function __construct($app) {
        $this->app = $app;
    }

    public function add($provider) {
        $this->providers[] = $provider;
    }

    public function initialize() {
        foreach ($this->providers as $provider) {
            $provider->initialize($this->app);
        }
    }
}