<?php
namespace Bono\Executor;

use Bono\Executor;
use Bono\Http\Request;
use Bono\Http\Headers;
use Bono\Http\Cookies;
use Bono\Http\Uri;
use Bono\Http\Context;
use ROH\Util\Injector;
use ROH\Util\Options;
use Bono\ErrorHandler;

class Web extends Executor
{
    protected static $STATUSES_EMPTY = [
        '204' => true,
        '205' => true,
        '304' => true,
    ];

    protected $errorHandler;

    public function __construct(Injector $injector, $bundleDef, array $options = [])
    {
        parent::__construct($injector, $bundleDef, (new Options([
            'output.buffer' => true,
            'route.dispatcher' => 'simple',
            'response.chunkSize' => 4096,
            'error.handler' => ErrorHandler::class,
        ]))->merge($options));

        $this->configureErrorHandler(
            empty($this['error.handler'])
                ? null
                : $injector->resolve($this['error.handler'])
        );
    }

    public function run(array $serverVars = null, array $cookieVars = null, $returns = false)
    {
        if (!is_null($this->errorHandler)) {
            $this->errorHandler->register();
        }

        if ($this['output.buffer']) {
            $level = ob_get_level();
            // start output buffer here
            ob_start();
        }

        $serverVars = $serverVars ?: $_SERVER;
        $cookieVars = $cookieVars ?: $_COOKIE;

        $uri = Uri::fromServerVars($serverVars);
        $headers = Headers::fromServerVars($serverVars);
        $cookies = new Cookies($cookieVars);
        $request = (new Request(@$serverVars['REQUEST_METHOD'] ?: 'GET', $uri, $headers))
            ->withCookies($cookies);
        $ctx = new Context($request);

        try {
            $this->dispatch($ctx);
        } catch (\Exception $e) {
            if (!$returns && !is_null($this->errorHandler)) {
                throw $e;
            }

            $ctx->setStatus(500)->getBody()->write($e->getMessage());
        }

        if ($this['output.buffer']) {
            // do not write directly to response or it will be cleaned
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        if (!$returns) {
            $this->respond($ctx);
        }

        return $ctx;
    }

    protected function respond(Context $context)
    {
        $response = $context->getResponse();

        if ($response->getBody()->getSize() === 0) {
            $context->setContentType('text/plain');
            $response->write($response->getReasonPhrase());
        }

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

            foreach ($response->getCookies()->getSets() as $name => $value) {
                call_user_func_array('setcookie', $value);
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

            $body->close();
        }
    }

    protected function configureErrorHandler($handler)
    {
        $this->errorHandler = $handler;
    }
}
