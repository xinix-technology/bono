<?php

namespace Bono\Provider;

class ProviderRepository {

    protected $app;
    protected $providers = array();

    function __construct($app) {
        $this->app = $app;
    }

    public function add(Provider $provider) {
        $provider->setApp($this->app);
        $this->providers[] = $provider;
    }

    public function initialize() {
        foreach ($this->providers as $provider) {
            $provider->initialize($this->app);
        }
    }
}