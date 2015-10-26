<?php

namespace Bono\Middleware;

use Bono\Http\Context;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;

class Notification extends UtilCollection
{
    protected $messages = [
        'error' => [
            '' => []
        ],
        'info' => [
            '' => []
        ],
    ];

    public function __construct(array $options = [])
    {
        $options = Options::create([])
            ->merge($options);

        parent::__construct($options);
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

    public function __invoke(Context $context, $next)
    {
        $context['notification'] = $this;

        return $next($context);
    }
}
