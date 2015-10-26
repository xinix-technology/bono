<?php
namespace Bono\Middleware;

use ROH\Util\Options;
use Bono\Http\Context;
use Bono\Middleware;

class BodyParser
{
    protected $parsers;

    public function __construct($options = [])
    {
        $this->options = Options::create([
            'allowedMethods' => [
                'POST' => true,
                'PUT' => true,
                'PATCH' => true,
            ],
            'parsers' => [
                'application/x-www-form-urlencoded' => [$this, 'formParser'],
                'multipart/form-data' => [$this, 'formParser'],
            ]
        ])
        ->merge($options);

        $this->parsers = $this->options['parsers'];
    }

    protected function formParser(Context $context)
    {
        if ($context->getRequest()->getOriginalMethod() === 'POST') {
            return $context->withParsedBody($_POST ?: []);
        } else {
            throw new \Exception('Unimplmeneted');
        }
    }

    public function __invoke(Context $context, $next)
    {
        if (isset($this->options['allowedMethods'][$context->getMethod()])) {
            $contentType = $context->getRequest()->getContentType();
            if (!isset($this->parsers[$contentType])) {
                throw new \Exception('Cannot found parser for '.$contentType);
            }
            $parser = $this->parsers[$contentType];
            $context = $parser($context);
        }

        $next($context);
    }
}
