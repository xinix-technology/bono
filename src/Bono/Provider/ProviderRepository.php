<?php

namespace Bono\Provider;

class ProviderRepository {

    protected $app;

    protected $providers = array();

    function __construct($app) {
        $this->app = $app;
    }

    public function add(Provider $provider) {
        $provider->setApplication($this->app);
        $this->providers[get_class($provider)] = $provider;
    }

    public function initialize() {
        foreach ($this->providers as $provider) {
            $provider->initialize();
        }
    }
}