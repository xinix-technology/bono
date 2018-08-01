<?php

namespace Bono\Middleware;

use Bono\Http\Context;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Exception;

class Notification
{
    public function query(Context $context, array $options)
    {
        $result = [];
        $levelMessages = $context['@notification.data'][$options['level']];
        if (null !== $levelMessages) {
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
        }

        return $result;
    }

    public function render(Context $context, array $options = null)
    {
        // low level unset because session already persisted
        $context->call('@session', 'remove', 'notification');

        if (null === $options) {
            return $this->render($context, ['level' => 'error']) . "\n" . $this->render($context, ['level' => 'info']);
        }

        $messages = $this->query($context, $options);
        // TODO should defined renderer?
        if (!empty($messages)) {
            $result = '<div class="notification__' . $options['level'] . '">';
            foreach ($messages as $message) {
                $result .= '<p>' . $message['message'] . '</p> ';
            }
            $result .= '</div>';
            return $result;
        }
    }

    public function notify(Context $context, array $message)
    {
        $level = isset($message['level']) ? $message['level'] : '';
        $notifyContext = isset($message['context']) ? $message['context'] : '';

        $notificationBag = $this->getData($context);
        $levelBag = $notificationBag[$level] ?: [];
        $levelBag[$notifyContext][] = $message;
        $notificationBag[$level] = $levelBag;
    }

    public function __invoke(Context $context, callable $next)
    {
        $context['@notification'] = $this;


        try {
            $next($context);
            $this->finalize($context);
        } catch (Exception $e) {
            $this->finalize($context);
            throw $e;
        }
    }

    protected function getData(Context $context)
    {
        if (null === $context['@notification.data']) {
            $context['@notification.data'] = new UtilCollection(
                $context->call('@session', 'get', 'notification') ?: []
            );
        }
        return $context['@notification.data'];
    }

    protected function finalize(Context $context)
    {
        $context->call(
            '@session',
            'set',
            'notification',
            $this->getData($context)->toArray()
        );
    }
}
