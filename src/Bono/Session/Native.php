<?php
namespace Bono\Session;

use Bono\Http\Context;

class Native
{
    public function __construct()
    {
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1);
        ini_set('session.gc_maxlifetime', 24 * 30 * 24 * 60 * 60);
        ini_set('session.use_cookies', 1);
    }

    public function getId(Context $context, array $options)
    {
        session_set_cookie_params(
            $options['lifetime'],
            $options['path'],
            $options['domain'],
            $options['secure'],
            $options['httpOnly']
        );
        session_name($options['name']);
        session_cache_limiter(false);
        session_start();
        // @session_start();

        return session_id();
    }

    public function read(Context $context)
    {
        return $_SESSION;
    }

    public function write(Context $context, $data)
    {
        $_SESSION = iterator_to_array($data);
        // foreach ($data as $key => $value) {
        //     $_SESSION[$key] = $value;
        // }
    }

    public function destroy(Context $context)
    {
        unset($_SESSION);
        session_unset();
        session_destroy();
        session_write_close();
        session_regenerate_id();
    }
}
