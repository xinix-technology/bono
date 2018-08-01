<?php
namespace Bono\Executor;

use Bono\Executor;
use Bono\Http\Request;
use Bono\Http\Uri;
use Bono\Http\Context;

class Test extends Executor
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Create request with method GET
     *
     * @param mixed $uri
     * @return Test
     */
    public function get($uri)
    {
        return $this->request('GET', $uri);
    }

    /**
     * Create request
     *
     * @param string $method
     * @param mixed $uri
     * @return Test
     */
    public function request($method, $uri)
    {
        if ($uri instanceof Uri === false) {
            $uri = Uri::fromUriString($uri);
        }

        $request = new Request($method, $uri);

        $clone = clone $this;
        $clone->context = new Context($request);
        return $clone;
    }

    /**
     * Run test request
     *
     * @return Context
     */
    public function run()
    {
        try {
            $this->dispatch($this->context);
        } catch (\Exception $err) {
            $this->context
                ->setStatus(500)
                ->getBody()->write($err->getMessage());
        } finally {
            return $this->context;
        }
    }
}
