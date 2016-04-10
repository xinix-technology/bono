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
    protected static $instance = null;

    protected static $STATUSES_EMPTY = [
        '204' => true,
        '205' => true,
        '304' => true,
    ];

    protected $bundle;

    public static function getInstance(array $options = [])
    {
        if (null === static::$instance) {
            static::$instance = new static($options);
        }

        return static::$instance;
    }

    public function __construct(array $options = [])
    {
        $this->singleton(static::class, $this);

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

        $this->configureErrorHandler();

    }

    public function isCli()
    {
        if (null === $this['cli']) {
            $this['cli'] = PHP_SAPI === 'cli';
        }
        return $this['cli'];
    }

    protected function configureErrorHandler()
    {
        if (!$this->isCli()) {
            $errorHandler = new ErrorHandler($this);
            $errorHandler->register();
        }
    }

    public function createContext()
    {
        $request = Request::getInstance($this->isCli());
        $response = new Response();
        $context = new Context($this, $request, $response);
        return $context;
    }

    public function run($useOutputBuffering = true)
    {
        if ($useOutputBuffering) {
            // start output buffer here
            ob_start();
        }

        date_default_timezone_set($this['date.timezone']);

        $context = $this->createContext();
        $context['route.bundle'] = $this->bundle;
        // try {
        $this->bundle->dispatch($context);
        // } catch (ContextException $e) {
        //     $context->handleError($e);
        // }

        if ($useOutputBuffering) {
            // do not write directly to response or it will be cleaned
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
        }

        $this->respond($context);
    }

    public function respond(Context $context)
    {
        $response = $context->getResponse();

        if ($response->getBody()->getSize() === 0) {
            $context->setContentType('text/plain');
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
        return $this->bundle->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->bundle->offsetUnset($offset);
    }

    public function getBundle()
    {
        return $this->bundle;
    }

}
