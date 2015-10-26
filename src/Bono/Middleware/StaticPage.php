<?php
namespace Bono\Middleware;

use Bono\Http\Context;

class StaticPage
{
    protected $extension = '.php';

    protected $prefix = 'static';

    public function __construct(array $options = [])
    {
        if (isset($options['extension'])) {
            $this->extension = $options['extension'];
        }

        if (isset($options['prefix'])) {
            $this->prefix = $options['prefix'];
        }
    }

    public function __invoke(Context $context, $next)
    {
        if (is_null($context['renderer'])) {
            $next($context);
            return;
        }

        $templatePath = $context['renderer']['templatePath'];

        $prefixPath = rtrim($templatePath.'/'.$this->prefix, '/');

        $path = $context->getUri()->getPath();
        if ($path === '/') {
            $path = '/index';
        }

        $file = $prefixPath . $path . $this->extension;

        if (is_readable($file)) {
            $context->withStatus(200)
                ->withHeader('Content-Type', 'text/html');
            $context['template'] = $this->prefix . $path;
        } else {
            $next($context);
        }
    }
}
