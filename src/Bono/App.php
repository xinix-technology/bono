<?php

namespace Bono;

use Slim\Slim;
use Bono\Provider\ProviderRepository;
use Doctrine\Common\Inflector\Inflector;

class App extends Slim {
    private $defaultConfig = array(
        'templates.path' => '../templates'
    );

    public function __construct(array $userSettings = array()) {

        parent::__construct($userSettings);

        $this->configure();

        $this->configureProvider();

        if ($this->config('autorun')) {
            $this->run();
        }
    }

    public function getNS($ns) {
        $exploded = explode('\\', $ns);
        foreach ($exploded as &$value) {
            $value = Inflector::classify($value);
        }
        $ns = implode('\\', $exploded);
        return $this->config('ns').'\\'.$ns;
    }

    private function configure() {
        $dh = opendir($this->config('config.path'));
        while (false !== ($entry = readdir($dh))) {
            if (strpos($entry, 'config-') === 0) {
                preg_match('/^config-(.*)\.php$/', $entry, $matches);
                $mode = $matches[1];
                $that = $this;
                $this->configureMode($mode, function() use ($mode, $that) {
                    $that->config($that->fetchConfig($mode));
                });
            }
        }
        closedir($dh);
    }

    private function configureProvider() {
        $this->providerRepository = new ProviderRepository($this);

        foreach($this->config('bono.providers') as $Provider) {
            $this->providerRepository->add(new $Provider());
        }

        $this->providerRepository->initialize();
    }

    public function fetchConfig($mode = '') {
        if (!$mode) {
            $mode = $this->config('mode');
        }

        $config = $modeConfig = array();
        if (is_readable($configFile = $this->config('config.path') . '/config.php')) {
            $config = include($configFile);
            if (!is_array($config)) {
                $config = array();
            }
        }
        if (is_readable($configFile = $this->config('config.path') . '/config-' . $mode . '.php')) {
            $modeConfig = include($configFile);
            if (!is_array($modeConfig)) {
                $modeConfig = array();
            }
        }

        $c = $modeConfig + $config + $this->defaultConfig;
        return $c;

    }

}