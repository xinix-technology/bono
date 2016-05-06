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
        $this->options = (new Options([
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
        ]))
        ->merge($options);

        $this->parsers = $this->options['parsers'];
    }

    protected function formParser($body)
    {
        parse_str($body, $result);
        return $result;
    }

    protected function jsonParser($body)
    {
        return json_decode($body, true);
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
            $context->setParsedBody($parser((string)$context->getRequest()->getBody()));
        }

        $next($context);
    }
}
