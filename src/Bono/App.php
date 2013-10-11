<?php

namespace Bono;

use Slim\Slim;
use Bono\Provider\ProviderRepository;
use Reekoheek\Util\Inflector;

class App extends Slim {
    private $defaultConfig = array(
        'templates.path' => '../templates'
    );

    public function onNotFound() {
        $errorTemplate = $this->config('templates.path').'/notFound.php';

        if (is_readable($errorTemplate)) {
            $this->render($errorTemplate, null, 404);
        } else {
            $this->response->setStatus(404);

            echo '<html>
            <head>
                <title></title>
            </head>
            <body>
                <h1>Ugly Not Found!</h1>

                <p>Edit this by creating templates/notFound.php</p>
            </body>
            </html>';
        }
    }

    public function onError(\Exception $e) {
        $errorCode = 500;
        if ($e instanceof \Bono\Exception\RestException) {
            $errorCode = $e->getCode();
        }

        if ($errorCode == 404) {
            $this->onNotFound();
            return;
        }

        $errorData = array(
            'stackTrace' => $e->getTraceAsString(),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        );

        $errorTemplate = $this->config('templates.path').'/error.php';

        if (is_readable($errorTemplate)) {
            $this->render($errorTemplate, $errorData, $errorCode);
        } else {
            $this->response->setStatus($errorCode);
            echo '<html>
            <head>
                <title></title>
            </head>
            <body>
                <h1>Ugly Error!</h1>

                <p>Edit this by creating templates/error.php</p>

                <label>Code</label>
                <div>'. $errorData['code'] .'</div>

                <label>Message</label>
                <div>'. $errorData['message'] .'</div>

                <label>File</label>
                <div>'. $errorData['file'] .'</div>

                <label>Line</label>
                <div>'. $errorData['line'] .'</div>

                <label>Stack Trace</label>
                <pre>'. $errorData['stackTrace'] .'</pre>
            </body>
            </html>';
        }
    }

    public function __construct(array $userSettings = array()) {

        parent::__construct($userSettings);

        $this->configure();

        $this->configureProvider();

        $that = $this;
        $this->error(function (\Exception $e) use ($that) {
            $that->onError($e);
        });
        $this->notFound(function () use ($that) {
            $that->onNotFound();
        });


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
        if (!is_readable($this->config('config.path'))) {
            return;
        }

        $dh = opendir($this->config('config.path'));

        if (!$dh) {
            return;
        }

        while (false !== ($entry = readdir($dh))) {
            if (strpos($entry, 'config-') === 0) {
                preg_match('/^config-(.*)\.php$/', $entry, $matches);
                $mode = $matches[1];

                $that = $this;
                $this->configureMode($mode, function() use ($mode,$that) {
                    $that->config($that->fetchConfig($mode, $that));
                });
            }
        }

        $logEnable = $this->config('log.enable');
        if (is_null($logEnable)) {
            $this->config('log.enable', true);
        }

        $this->config('bono.debug', $this->config('debug'));
        $this->config('debug', false);

        closedir($dh);
    }

    private function configureProvider() {
        $this->providerRepository = new ProviderRepository($this);

        $providers = $this->config('bono.providers') ?: array();
        foreach($providers as $Provider) {
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