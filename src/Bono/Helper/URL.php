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
}
