<?php

namespace Bono\Middleware;

use Bono\Http\Context;
use ROH\Util\Options;
use ROH\Util\Collection as UtilCollection;
use Exception;
use Bono\Session\Native;

class Session
{
    protected $options;

    protected $adapter;

    public function __construct(Executor $executor, array $options = [])
    {
        $this->executor = $executor;

        $this->options = (new Options([
            'name' => 'BSESS',
            'lifetime' => '2 years',
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httpOnly' => false,
            'adapter' => [ Native::class ],
            'autoRefresh' => false,
        ]))->merge($options)->toArray();

        if (is_string($this->options['lifetime'])) {
            $this->options['lifetime'] = strtotime($this->options['lifetime']) - time();
        }

        $this->adapter = $app->getInjector()->resolve($this->options['adapter']);
        unset($this->options['adapter']);
    }

    public function __invoke(Context $context, callable $next)
    {
        if ($this->executor['process.cli']) {
            return $next($context);
        }

        $this->start($context);

        try {
            $next($context);
        } catch (Exception $e) {
            $lastError = $e;
        }

        $this->stop($context);

        if (isset($lastError)) {
            throw $lastError;
        }
    }

    protected function start(Context $context, $keep = false)
    {
        // it sure that it wont be called more than once since it is protected not public
        // if (session_id()) {
        //     return;
        // }
        $context['@session'] = $this;

        $options = $this->options;
        $options['path'] = rtrim(rtrim($context['original.uri']->getBasepath(), '/index.php'), '/') . '/';

        if (!$keep) {
            $options['lifetime'] = $context->getCookie('keep') ?: 0;
        }

        $context['@session.options'] = $options;

        $context['@session.id'] = $this->adapter->getId($context, $options);

        if ($options['lifetime'] > 0) {
            $context->setCookie(
                $options['name'],
                $context['@session.id'],
                time() + $options['lifetime'],
                $options['path']
            );
            $context->setCookie('keep', $options['lifetime'], time() + $options['lifetime'], $options['path']);
        } else {
            $context->setCookie($options['name'], $context['@session.id'], 0, $options['path']);
        }

        $context['@session.data'] = new UtilCollection($this->adapter->read($context));
    }

    protected function stop(Context $context)
    {
        $this->adapter->write($context, $context['@session.data']);
        $context['@session.written'] = true;
    }

    protected function destroy(Context $context)
    {
        $options = $context['@session.options'];

        $this->adapter->destroy($context);

        $context->removeCookie($options['name'], $options['path']);
        $context->removeCookie('keep', $options['path']);

        unset($context['@session.options']);
    }

    public function reset(Context $context, $keep = false)
    {
        $this->destroy($context);
        $this->start($context, $keep);
    }

    public function get(Context $context, $key, $default = null)
    {
        if (isset($context['@session.data'][$key])) {
            return $context['@session.data'][$key];
        } else {
            return $default;
        }
    }

    public function set(Context $context, $key, $value)
    {
        $context['@session.data'][$key] = $value;
        if (null !== $context['@session.written']) {
            $this->adapter->write($context, $context['@session.data']);
        }
    }

    public function remove(Context $context, $key)
    {
        unset($context['@session.data'][$key]);
        if (null !== $context['@session.written']) {
            $this->adapter->write($context, $context['@session.data']);
        }
    }
}
