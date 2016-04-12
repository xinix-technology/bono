<?php
namespace Bono\Helper;

use Bono\Http\Uri;

class Url
{
    public static function bundle($pathname, $relativeTo)
    {
        $uri = $relativeTo->withPathname(trim($pathname, '/'))->withBasePath($relativeTo->getBasePath());

        $uri = $uri->withQuery('');
        return $uri->__toString();
    }

    public static function asset($pathname, $relativeTo)
    {
        return $relativeTo->withPathname(trim($pathname, '/'))->withBasePath('')->__toString();
    }

    public static function format($path, $meta)
    {
        return preg_replace_callback('~{([^}]+)}~', function ($matches) use ($meta) {
            $key = $matches[1];
            return isset($meta[$key]) ? $meta[$key] : '';
        }, $path);
    }
}
