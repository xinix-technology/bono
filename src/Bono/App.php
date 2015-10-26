<?php

namespace Bono;

use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Exception\ContextException;
use ROH\Util\Options;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;

class App extends Bundle
{
    protected static $instance;

    protected static $STATUSES_EMPTY = [
        '204' => true,
        '205' => true,
        '304' => true,
    ];

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
            // 'response.contentType' => 'text/html',
        ];
        $optionsPath = isset($options['config.path']) ? $options['config.path'] : $mergedOptions['config.path'];

        $mergedOptions = Options::create($mergedOptions, $mergedOptions['env'])
            ->mergeFile($optionsPath . '/config.php')
            ->merge($options)
            ->toArray();

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
            $handler = new PrettyPageHandler();
            $handler->addResourcePath(__DIR__.'/../../templates/vendor/whoops');
            $whoops->pushHandler($handler);
            $whoops->pushHandler(function () use ($handler) {
                $obs = [];
                while (ob_get_level() > 0) {
                    $ob = trim(ob_get_contents());
                    if ($ob) {
                        $obs[] = $ob;
                    }
                    ob_end_clean();
                }
                $handler->addDataTable('Output Buffers', $obs);
            });
            $whoops->register();
        }
    }

    // protected function handleError($errno, $errstr = '', $errfile = '', $errline = '')
    // {
    //     if (!($errno & error_reporting())) {
    //         return;
    //     }

    //     throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    // }

    public function createContext()
    {
        $request = Request::getInstance($this->isCli());
        $response = new Response(404);
        $context = new Context($request, $response);
        return $context;
    }

    public function run()
    {
        date_default_timezone_set($this['date.timezone']);

        $context = $this->createContext();
        try {
            $this->dispatch($context);
        } catch (ContextException $e) {
            $context->handleError($e);
        }

        $this->respond($context);
    }

    public function respond(Context $context)
    {
        $response = $context->getResponse();

        if ($response->getBody()->getSize() === 0) {
            $context->withContentType('text/plain');
            $context->write($response->getReasonPhrase());
        }

        $headers = $response->getHeaders();

        // remove default content-type
        // if (is_null($headers['Content-Type']) && isset($this['response.contentType'])) {
        //     $headers['Content-Type'] = $this['response.contentType'];
        // }

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

        if (!isset(static::$STATUSES_EMPTY[$response->getStatusCode()])) {
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

        // see koa for the rest
    }
}
