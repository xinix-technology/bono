<?php
namespace Bono\Http;

use Psr\Http\Message\UriInterface;
use Bono\Exception\BonoException;

class Uri implements UriInterface
{
    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';

    private static $charSubDelims = '!\$&\'\(\)\*\+,;=';

    private static $replaceQuery = ['=' => '%3D', '&' => '%26'];

    protected $scheme = '';

    protected $user = '';

    protected $password = '';

    protected $host = '';

    protected $port;

    protected $basePath = '';

    protected $path = '';

    protected $query = '';

    protected $fragment = '';

    protected $pathname = '';

    protected $extension = null;

    public static function byEnvironment($var, $cli = false)
    {
        if ($cli) {
            return new static('', '', null, '/'. implode('/', array_slice($var['argv'], 1)));
        }

        // Scheme
        if (isset($var['HTTP_X_FORWARDED_PROTO'])) {
            $scheme = $var['HTTP_X_FORWARDED_PROTO']; // Will be "http" or "https"
        } else {
            $scheme = (empty($var['HTTPS']) || $var['HTTPS'] === 'off') ? 'http' : 'https';
        }

        // Authority: Username and password
        $username = isset($var['PHP_AUTH_USER']) ? $var['PHP_AUTH_USER'] : null;
        $password = isset($var['PHP_AUTH_PW']) ? $var['PHP_AUTH_PW'] : null;

        // Authority: Host
        if (isset($var['HTTP_X_FORWARDED_HOST'])) {
            $host = trim(current(explode(',', $var['HTTP_X_FORWARDED_HOST'])));
        } elseif (isset($var['HTTP_HOST'])) {
            $host = $var['HTTP_HOST'];
        } elseif (isset($var['SERVER_NAME'])) {
            $host = $var['SERVER_NAME'];
        } else {
            $host = '127.0.0.1';
        }

        // Authority: Port
        $pos = strpos($host, ':');
        if ($pos !== false) {
            $port = (int)substr($host, $pos + 1);
            $host = strstr($host, ':', true);
        } else {
            $port = isset($var['SERVER_PORT']) ? (int) $var['SERVER_PORT'] : 80;
        }

        // Path
        $requestScriptName = parse_url($var['SCRIPT_NAME'], PHP_URL_PATH);
        $requestScriptDir = dirname($requestScriptName);
        $requestUri = parse_url(isset($var['REQUEST_URI']) ? $var['REQUEST_URI'] : '/', PHP_URL_PATH);
        $basePath = '';

        if (stripos($requestUri, $requestScriptName) === 0) {
            $basePath = $requestScriptName;
        // } elseif ($requestScriptDir !== '/' && stripos($requestUri, $requestScriptDir) === 0) {
        //     // $basePath = $requestScriptDir;
        //     throw new \Exception('Never been here yet?');
        }

        if ($basePath) {
            $virtualPath = substr($requestUri, strlen($basePath));
        } else {
            $virtualPath = $requestUri;
        }

        // Query string
        $queryString = isset($var['QUERY_STRING']) ? $var['QUERY_STRING'] : '';

        // Fragment
        $fragment = '';

        $uri = new static($scheme, $host, $port, $virtualPath, $queryString, $fragment, $username, $password);
        if ($basePath) {
            $uri = $uri->withBasePath($basePath);
        }

        return $uri;
    }

    public function __construct(
        $scheme = 'http',
        $host = '127.0.0.1',
        $port = 80,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = null,
        $password = null
    ) {
        $this->scheme = $this->filterScheme($scheme);
        $this->host = $host;
        $this->port = $this->filterPort($port);
        $this->path = empty($path) ? '/' : $this->filterPath($path);
        $this->query = $this->filterQuery($query);
        $this->fragment = $this->filterQuery($fragment);
        $this->user = $user;
        $this->password = $password;

        $this->calculatePath($this->path);
    }

    private function filterQueryAndFragment($str)
    {
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $str
        );
    }

    private function rawurlencodeMatchZero(array $match)
    {
        return rawurlencode($match[0]);
    }

    // uriinterface
    public function getExtension()
    {
        return $this->extension;
    }

    public function getPathname()
    {
        return $this->pathname;
    }

    protected function calculatePath($path)
    {
        $pathInfo = pathinfo($path);
        if (isset($pathInfo['extension'])) {
            $this->extension = $pathInfo['extension'];

            // when pathinfo[dirname] is /, then / + pathinfo[filename] is the true pathname
            if ($pathInfo['dirname'] === '/') {
                $this->pathname = '/'.$pathInfo['filename'];
            } else {
                $this->pathname = $pathInfo['dirname'].'/'.$pathInfo['filename'];
            }
        } else {
            $this->pathname = $path;
        }
    }

    public function withPathname($pathname)
    {
        if (!is_string($pathname)) {
            throw new BonoException('Uri pathname must be a string');
        }

        return $this->withPath($pathname . (isset($this->extension) ? '.' . $this->extension : ''));
    }

    public function shift($path)
    {
        $uri = clone $this;
        if ('/' !== $path) {
            $newPath = substr($this->getPath(), strlen($path));
            if (isset($this->extension) && ('.' . $this->extension) === $newPath) {
                $newPath = '';
            }
            $uri = clone $this
                ->withPath($newPath ?: '')
                ->withBasePath($this->getBasePath().$path);
        }
        return $uri;
    }

    public function unshift($path)
    {
        $uri = clone $this;
        if ($path !== '/') {
            $segments = explode('/', $this->basePath);
            $lastSegment = array_pop($segments);
            $uri = $this
                ->withPath('/'.$lastSegment)
                ->withBasePath(implode('/', $segments));
        }
        return $uri;
    }

    protected function filterScheme($scheme)
    {
        static $valid = [
            '' => true,
            'https' => true,
            'http' => true,
        ];

        if (!is_string($scheme) && !method_exists($scheme, '__toString')) {
            throw new BonoException('Uri scheme must be a string');
        }

        $scheme = str_replace('://', '', strtolower((string)$scheme));
        if (!isset($valid[$scheme])) {
            throw new BonoException('Uri scheme must be one of: "", "https", "http"');
        }

        return $scheme;
    }

    protected function filterPort($port)
    {
        if (is_null($port) || (is_integer($port) && ($port >= 1 && $port <= 65535))) {
            return $port;
        }

        throw new BonoException('Uri port must be null or an integer between 1 and 65535 (inclusive)');
    }

    protected function filterQuery($query)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $query
        );
    }

    protected function filterPath($path)
    {
        return preg_replace_callback(
            '/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/',
            [$this, 'rawurlencodeMatchZero'],
            $path
        );
    }

    public function withBasePath($basePath)
    {
        if (!is_string($basePath)) {
            throw new BonoException('Uri path must be a string');
        }
        if (!empty($basePath)) {
            $basePath = '/' . trim($basePath, '/'); // <-- Trim on both sides
        }
        $clone = clone $this;

        if ($basePath !== '/') {
            $clone->basePath = $this->filterPath($basePath);
        }

        return $clone;
    }

    // uri interface
    public function getScheme()
    {
        return $this->scheme;
    }

    public function getAuthority()
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        return ($userInfo ? $userInfo . '@' : '') . $host . ($port !== null ? ':' . $port : '');
    }

    public function getUserInfo()
    {
        return $this->user . ($this->password ? ':' . $this->password : '');
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        $new = clone $this;
        $new->scheme = $scheme;
        return $new;
    }

    public function withUserInfo($user, $password = null)
    {
        $clone = clone $this;
        $clone->user = $user;
        $clone->password = $password ? $password : '';
        return $clone;
    }

    public function withHost($host)
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function withPort($port)
    {
        $port = $this->filterPort($port);
        $clone = clone $this;
        $clone->port = $port;
        return $clone;

    }

    public function withPath($path)
    {
        if (!is_string($path)) {
            throw new BonoException('Uri path must be a string');
        }

        $clone = clone $this;
        $clone->path = $this->filterPath($path);

        // if the path is absolute, then clear basePath
        if (substr($path, 0, 1) == '/') {
            $clone->basePath = '';
        }

        $clone->calculatePath($clone->path);

        return $clone;

    }

    public function withQuery($query)
    {
        if (!is_string($query) && !method_exists($query, '__toString')) {
            throw new BonoException('Query string must be a string');
        }

        $query = (string) $query;
        if (substr($query, 0, 1) === '?') {
            $query = substr($query, 1);
        }

        $query = $this->filterQueryAndFragment($query);

        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;
        return $new;
    }

    public function withFragment($fragment)
    {
        if (!is_string($fragment)) {
            throw new BonoException('Uri fragment must be a string');
        }
        $fragment = ltrim($fragment, '#');
        $clone = clone $this;
        $clone->fragment = $this->filterQuery($fragment);
        return $clone;
    }

    public function __toString()
    {
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $basePath = $this->getBasePath();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        $path = rtrim($basePath . '/' . ltrim($path, '/'), '/');

        return ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . $path
            . ($query ? '?' . $query : '')
            . ($fragment ? '#' . $fragment : '');
    }

    public function __debugInfo()
    {
        return [
            'uri' => $this->__toString(),
            'base' => $this->basePath,
            'ext' => $this->extension,
        ];
    }
}
