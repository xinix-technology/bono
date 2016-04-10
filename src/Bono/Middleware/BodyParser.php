<?php
namespace Bono\Middleware;

use Bono\Exception\BonoException;
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
                'DELETE' => true,
            ],
            'parsers' => [
                'application/x-www-form-urlencoded' => [$this, 'formParser'],
                'multipart/form-data' => [$this, 'formParser'],
                'application/json' => [$this, 'jsonParser'],
            ]
        ])
        ->merge($options);

        $this->parsers = $this->options['parsers'];
    }

    protected function formParser(Context $context)
    {
        if ($context->getRequest()->getOriginalMethod() === 'POST') {
            return $context->setParsedBody($_POST ?: []);
        // } else {
        //     throw new \Exception('Unimplemented yet!');
        }

        throw new BonoException('Cannot parse form if original method not POST');
    }

    protected function jsonParser(Context $context)
    {
        $body = (string)$context->getRequest()->getBody();
        return $context->setParsedBody(json_decode($body, true));
    }

    public function __invoke(Context $context, $next)
    {
        $context['@bodyParser'] = $this;

        if (isset($this->options['allowedMethods'][$context->getMethod()])) {
            $contentType = $context->getRequest()->getContentType();
            if (!isset($this->parsers[$contentType])) {
                throw new BonoException('Cannot found parser for ' . $contentType);
            }
            $parser = $this->parsers[$contentType];
            $context = $parser($context);
        }

        $next($context);
    }
}
