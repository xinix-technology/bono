<?php

namespace Bono;

use Bono\Http\Context;
use Bono\Http\Request;
use Bono\Http\Response;
use Bono\Http\Headers;
use Bono\Exception\ContextException;
use ROH\Util\Options;
use Bono\ErrorHandler;
use ROH\Util\Injector;
use Bono\Http\Uri;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;

class App extends Bundle
{
    protected static $instance = null;

    protected static $STATUSES_EMPTY = [
        '204' => true,
        '205' => true,
        '304' => true,
    ];

    protected $envVars;

    protected $bundle;

    protected $loggers;

    protected $defaultLogger;

    protected $errorHandler;

    public static function getInstance(array $options = [])
    {
        if (null === static::$instance) {
            static::$instance = new static($options);
        }

        return static::$instance;
    }

    public function __construct(array $options = [])
    {
        Injector::getInstance()->singleton(static::class, $this);

        $this->envVars = $_SERVER;

        // configure
        $defaultOptions = [
            'env' => isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'development',
            'config.path' => '../config',
            'date.timezone' => 'UTC',
            'route.dispatcher' => 'simple',
            'response.chunkSize' => 4096,
            'error.handler' => ErrorHandler::class,
            // 'response.contentType' => 'text/html',
        ];
        $optionsPath = isset($options['config.path']) ? $options['config.path'] : $defaultOptions['config.path'];

        Options::setEnv($defaultOptions['env']);

        $options = (new Options($defaultOptions))
            ->mergeFile($optionsPath . '/config.php')
            ->merge($options)
            ->toArray();

        date_default_timezone_set($options['date.timezone']);

        parent::__construct($this, $options);

        $this->configureErrorHandler();

        $this->configureLoggers();
    }

    public function getErrorHandler()
    {
        return $this->errorHandler;
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
            $ErrorHandler = $this['error.handler'] ?: 'ErrorHandler';
            $this->errorHandler = new $ErrorHandler($this);
        }
    }

    protected function configureLoggers()
    {
        if (is_array($this['loggers'])) {
            foreach ($this['loggers'] as $key => $value) {
                $this->addLogger($key, Injector::getInstance()->resolve($value));
            }
        }
    }

    public function createContext()
    {
        $uri = Uri::byEnvironment($this->envVars, $this->isCli());
        $headers = Headers::byEnvironment($this->envVars);
        $request = new Request($this->isCli() ? 'GET' : $this->envVars['REQUEST_METHOD'], $uri, $headers);
        $response = new Response();
        $context = new Context($this, $request, $response);
        return $context;
    }

    public function run($useOutputBuffering = true)
    {
        if ($useOutputBuffering) {
            $level = ob_get_level();
            // start output buffer here
            ob_start();
        }

        $context = $this->createContext();
        $context['route.bundle'] = $this;
        // try {
        $this->dispatch($context);
        // } catch (ContextException $e) {
        //     $context->handleError($e);
        // }

        if ($useOutputBuffering) {
            // do not write directly to response or it will be cleaned
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        $this->respond($context);
    }

    protected function respond(Context $context, &$filename = null)
    {
        $response = $context->getResponse();

        if ($response->getBody()->getSize() === 0) {
            $context->setContentType('text/plain');
            $response->write($response->getReasonPhrase());
        }

        $headers = $response->getHeaders();
        if (!headers_sent() || isset($GLOBALS['test-coverage'])) {
            @header(sprintf(
                'HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ));

            foreach ($response->getHeaders()->normalize() as $name => $value) {
                @header(sprintf('%s: %s', $name, implode(', ', $value ?: [])), false);
            }
        }

        if (!isset(static::$STATUSES_EMPTY[$response->getStatusCode()])) {
            $body = $response->getBody();

            if ($body->isSeekable()) {
                $body->rewind();
            }

            while (!$body->eof()) {
                if (connection_status() === CONNECTION_NORMAL) {
                    echo $body->read($this['response.chunkSize']);
                }
            }
        }
    }

    public function addLogger($name, LoggerInterface $logger)
    {
        $name = $name ?: 'bono';

        $this->loggers[$name] = $logger;

        if (null === $this->defaultLogger) {
            $this->defaultLogger = $name;
        }

        return $this;
    }

    public function getLogger($name = null)
    {
        if (null === $this->loggers) {
            $this->loggers = [];
            $logger = new Logger('bono');
            $logger->pushHandler(new ErrorLogHandler());
            $this->addLogger($name, $logger);
        }

        $name = $name ?: $this->defaultLogger;

        return isset($this->loggers[$name]) ? $this->loggers[$name] : null;
    }

}
