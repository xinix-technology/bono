<?php
namespace Bono\Middleware;

use ROH\Util\Options;

class BodyParser
{
    protected $parsers;

    public function __construct($options = [])
    {
        $this->options = Options::create([
            'allowedMethods' => [
                'POST' => null,
                'PUT' => null,
                'PATCH' => null,
            ],
            'parsers' => [
                'application/x-www-form-urlencoded' => [$this, 'formParser'],
                'multipart/form-data' => [$this, 'formParser'],
            ]
        ])
        ->merge($options);

        $this->parsers = $this->options['parsers'];
    }

    protected function formParser($request)
    {
        if ($request->getOriginalMethod() === 'POST') {
            return $request->withParsedBody($_POST ?: []);
        } else {
            throw new \Exception('Unimplmeneted');
        }
    }

    public function __invoke($request, $next)
    {
        if (array_key_exists($request->getMethod(), $this->options['allowedMethods'])) {
            $contentType = $request->getContentType();
            if (!isset($this->parsers[$contentType])) {
                throw new \Exception('Cannot found parser for '.$contentType);
            }
            $parser = $this->parsers[$contentType];
            $request = $parser($request);
        }
        return $next($request);
    }
}
