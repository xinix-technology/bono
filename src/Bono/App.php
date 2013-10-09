<?php

namespace Bono;

use Slim\Slim;
use Bono\Provider\ProviderRepository;
use Reekoheek\Util\Inflector;

class App extends Slim {
    private $defaultConfig = array(
        'templates.path' => '../templates'
    );

    public function __construct(array $userSettings = array()) {

        parent::__construct($userSettings);

        $this->configure();

        $this->configureProvider();

        $this->error(function (\Exception $e) {

            $errorCode = 500;
            if ($e instanceof \Bono\Exception\RestException) {
                $errorCode = $e->getCode();
            }

            $errorData = array(
                'stackTrace' => $e->getTraceAsString(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            );

            if ($errorCode != 404) {
                $errorTemplate = $this->config('templates.path').'/error.php';
            } else {
                $errorTemplate = $this->config('templates.path').'/404.php';
            }

            if (is_readable($errorTemplate)) {
                $this->render($errorTemplate, $errorData, $errorCode);
            } else {
                $this->response->setStatus($errorCode);
                if ($errorCode != 404) {
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
                } else {
                    echo '<html>
                    <head>
                        <title></title>
                    </head>
                    <body>
                        <h1>Ugly Not Found!</h1>

                        <p>Edit this by creating templates/404.php</p>
                    </body>
                    </html>';
                }
            }
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
        $dh = opendir($this->config('config.path'));
        while (false !== ($entry = readdir($dh))) {
            if (strpos($entry, 'config-') === 0) {
                preg_match('/^config-(.*)\.php$/', $entry, $matches);
                $mode = $matches[1];
                $this->configureMode($mode, function() use ($mode) {
                    $this->config($this->fetchConfig($mode));
                });
            }
        }

        $this->config('bono.debug', $this->config('debug'));
        $this->config('debug', false);

        closedir($dh);
    }

    private function configureProvider() {
        $this->providerRepository = new ProviderRepository($this);

        foreach($this->config('bono.providers') as $Provider) {
            $this->providerRepository->add(new $Provider());
        }

        $this->providerRepository->initialize();
    }

    private function fetchConfig($mode = '') {
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