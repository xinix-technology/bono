<?php

namespace Bono;

use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Exception\ContextException;
use ROH\Util\Options;
use Bono\ErrorHandler;
use ROH\Util\Injector;
use ArrayAccess;

class App extends Injector implements ArrayAccess
{
    protected static $instance;

    protected static $STATUSES_EMPTY = [
        '204' => true,
        '205' => true,
        '304' => true,
    ];

    protected $bundle;

    public static function getInstance(array $options = [])
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($options);
        }

        return static::$instance;
    }

    public function __construct(array $options = [])
    {
        // start here end on respond
        ob_start();

        $this->configureErrorHandler();

        // configure
        $defaultOptions = [
            'env' => isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'development',
            'config.path' => '../config',
            'date.timezone' => 'UTC',
            'route.dispatcher' => 'simple',
            'response.chunkSize' => 4096,
            // 'response.contentType' => 'text/html',
        ];
        $optionsPath = isset($options['config.path']) ? $options['config.path'] : $defaultOptions['config.path'];

        Options::setEnv($defaultOptions['env']);

        $options = Options::create($defaultOptions)
            ->mergeFile($optionsPath . '/config.php')
            ->merge($options)
            ->toArray();

        $this->bundle = new Bundle($this, $options);

        $this->singleton(static::class, $this);
    }

    public function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    protected function configureErrorHandler()
    {
        if (!$this->isCli()) {
            $errorHandler = new ErrorHandler($this);
            $errorHandler->register();
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
        $context = new Context($this, $request, $response);
        return $context;
    }

    public function run()
    {
        date_default_timezone_set($this['date.timezone']);

        $context = $this->createContext();
        $context['route.bundle'] = $this->bundle;
        try {
            $this->bundle->dispatch($context);
        } catch (ContextException $e) {
            $context->handleError($e);
        }

        $this->respond($context);
    }

    public function respond(Context $context)
    {
        // do not write directly to response or it will be cleaned
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $response = $context->getResponse();

        if ($response->getBody()->getSize() === 0) {
            $context->withContentType('text/plain');
            $response->write($response->getReasonPhrase());
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

    public function handleError($level, $message, $file = null, $line = null)
    {
        if ($level & error_reporting()) {
            return new ErrorException($message, /*code*/ $level, /*severity*/ $level, $file, $line);
        }
    }


    public function offsetExists($offset)
    {
        return $this->bundle->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->bundle->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->bundle->offsetSet($offset);
    }

    public function offsetUnset($offset)
    {
        return $this->bundle->offsetUnset($offset);
    }

}
