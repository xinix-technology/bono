<?php

namespace TestCase;

require __DIR__ . '/../../vendor/autoload.php';

use Slim\Environment;
use Bono\App;

class BonoTestCase extends \PHPUnit_Framework_TestCase
{

    private $testingMethods = array('get', 'post', 'patch', 'put', 'delete', 'head');

    private function config()
    {
        $list   = null;
        $app    = \Bono\App::getInstance();
        $config = array();
        $path   = dirname(__DIR__) . '/config/chunks';

        if ($dh = opendir($path) and is_dir($path)) {
            while (($fileName = readdir($dh)) !== false) {
                if (is_file($path . DIRECTORY_SEPARATOR . $fileName)) {
                    $content = require($path . DIRECTORY_SEPARATOR . $fileName);
                    if (is_array($content)) {
                        $config = array_merge_recursive($config, $content);
                    }
                }
            }

            closedir($dh);
        }

        $c = array(
            'autorun'            => false,
            'app.templates.path' => dirname(__DIR__) . '/templates',
        );

        return array_merge_recursive($c, $config);
    }

    private function request($method, $path, $formVars = array(), $optionalHeaders = array())
    {

        // Prepare a mock environment
        Environment::mock(array_merge(array(
            'REQUEST_METHOD' => strtoupper($method),
            'PATH_INFO'      => $path,
            'slim.input'     => http_build_query($formVars)
        ), $optionalHeaders));

        $this->app = new App($this->config());

        $this->app->theme->addBaseDirectory(dirname(__DIR__), 5);

        $this->app->get('/', function () {
            return true;
        });

        // Capture STDOUT
        ob_start();

        // Execute our app
        $this->app->run();

        // Clean buffer
        ob_get_clean();

        // Establish some useful references to the slim app properties
        $this->request  = $this->app->request();
        $this->response = $this->app->response();

        return $this->app->response;
    }

    // Implement our `get`, `post`, and other http operations
    public function __call($method, $arguments)
    {
        if (in_array($method, $this->testingMethods)) {
            list($path, $formVars, $headers) = array_pad($arguments, 3, array());
            return $this->request($method, $path, $formVars, $headers);
        }
        throw new \BadMethodCallException('[' . strtoupper($method) . '] is not supported');
    }
}
