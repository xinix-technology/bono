<?php

namespace Bono\Middleware;

use ROH\Util\Options;
use Bono\Http\Response;

class Notification
{
    protected $options;

    protected $messages = [
        'error' => [
            '' => []
        ],
        'info' => [
            '' => []
        ],
    ];

    public function __construct($options = [])
    {
        $this->options = Options::create([])
            ->merge($options);
    }

    public function query(array $options)
    {
        $result = [];
        $levelMessages = $this->messages[$options['level']];
        if (isset($options['context'])) {
            if (isset($levelMessages[$options['context']])) {
                $result = $levelMessages[$options['context']];
            }
        } else {
            foreach ($levelMessages as $messages) {
                foreach ($messages as $message) {
                    $result[] = $message;
                }
            }
        }

        return $result;
    }

    public function notify(array $message)
    {
        $level = isset($message['level']) ? $message['level'] : '';
        $context = isset($message['context']) ? $message['context'] : '';
        $this->messages[$level][$context][] = $message;
    }

    public function __invoke($request, $next)
    {
        $request['notification'] = $this;
        return $next($request);
    }
}
