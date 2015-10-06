<?php
namespace Bono\Middleware;

use ROH\Util\Options;
use Bono\App;
use Bono\Http\Response;

class StaticPage
{

    public function __construct($options = [])
    {
        $this->options = Options::create([
            'extension' => '.php',
            'prefix' => 'static',
        ])
        ->merge($options);
    }

    public function __invoke($request, $next)
    {
        $app = App::getInstance();
        $templatePath = $request['$templateRenderer']->getOption('templatePath');

        $prefixPath = rtrim($templatePath.'/'.$this->options['prefix'], '/');

        $path = $request->getUri()->getPath();
        if ($path === '/') {
            $path = '/index';
        }

        $file = $prefixPath . $path . $this->options['extension'];
        if (is_readable($file)) {
            $response = (new Response(200))->withHeader('content-type', 'text/html');
            // $response['bundle'] = $request;
            $response['request'] = $request;
            $response['template'] = $this->options['prefix'] . $path;
        } else {
            $response = $next($request);
        }

        return $response;
    }
}
