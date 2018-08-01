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

class Cli extends Executor
{
    public function __construct(Injector $injector, $bundleDef, array $options = [])
    {
        parent::__construct($injector, $bundleDef, (new Options([
            'output.buffer' => false,
            'response.chunkSize' => 4096,
        ]))->merge($options));
    }

    public function run(array $argv)
    {
        if ($this['output.buffer']) {
            $level = ob_get_level();
            // start output buffer here
            ob_start();
        }

        $uri = Uri::fromCommandArgv($argv);
        $request = new Request('GET', $uri);
        $ctx = new Context($request);

        try {
            $this->dispatch($ctx);
        } catch (\Exception $e) {
            $ctx->setStatus(500)->getBody()->write($e->getMessage());
        }

        if ($this['output.buffer']) {
            // do not write directly to response or it will be cleaned
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
        }

        return $ctx;
    }
}
