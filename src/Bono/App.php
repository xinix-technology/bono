<?php

namespace Bono;

use Bono\Http\Request;
use Bono\Http\Response;
use ROH\Util\Options;
use Whoops\Run as WhoopsRun;

class App extends Bundle
{
    protected static $instance;

    public static function getInstance(array $options = array())
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($options);
        }

        return static::$instance;
    }

    public function __construct($options = array())
    {
        $this->configureErrorHandler();

        // configure
        $mergedOptions = [
            'env' => isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'development',
            'config.path' => '../config',
            'date.timezone' => 'UTC',
            'route.dispatcher' => 'simple',
            'response.chunkSize' => 4096,
            'response.contentType' => 'text/html',
        ];
        $optionsPath = isset($options['config.path']) ? $options['config.path'] : $mergedOptions['config.path'];

        $mergedOptions = Options::create($mergedOptions, $mergedOptions['env'])
            ->mergeFile($optionsPath . '/config.php')
            ->merge($options);
            // ->toArray();

        parent::__construct($mergedOptions);
    }

    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    protected function configureErrorHandler()
    {
        if (!$this->isCli()) {
            $whoops = new WhoopsRun();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }
    }

    protected function handleError($errno, $errstr = '', $errfile = '', $errline = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }

        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }

    public function run()
    {
        date_default_timezone_set($this['date.timezone']);

        $request = Request::getInstance($this->isCli());

        $response = $this->dispatch($request);

        $this->respond($response);
    }

    public function respond(Response $response = null)
    {
        if (is_null($response)) {
            return;
        }

        $headers = $response->getHeaders();
        if (is_null($headers['content-type']) && isset($this['response.contentType'])) {
            $headers['Content-Type'] = $this['response.contentType'];
        }

        if (!headers_sent()) {
            header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders()->normalize() as $name => $value) {
                header(sprintf('%s: %s', $name, implode(', ', $value ?: [])), false);
            }
        }

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (!$body->eof()) {
            echo $body->read($this['response.chunkSize']);
            if (connection_status() != CONNECTION_NORMAL) {
                break;
            }
        }
    }
}

if (!function_exists('app')) {
    function app()
    {
        return App::getInstance();
    }
}
