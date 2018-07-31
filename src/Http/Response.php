<?php
namespace Bono\Http;

use Psr\Http\Message\ResponseInterface;
use ROH\Util\Collection;
use Bono\Exception\BonoException;

class Response extends Message implements ResponseInterface
{
    public static $messages = [
        //Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        //Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        //Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        //Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        //Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    protected $status;

    protected $reasonPhrase;

    public function __construct($status = 404, Headers $headers = null, $body = null)
    {
        $this->status = $status;

        if (is_string($body)) {
            $this->write($body);
        } else {
            $this->body = $body;
        }

        parent::__construct($headers);
    }

    public function write($str)
    {
        $this->getBody()->write($str);

        return $this;
    }

    public function getBody()
    {
        if (null === $this->body) {
            $this->body = new Stream();
        }

        return $this->body;
    }

    // public function getError()
    // {
    //     return $this->error;
    // }

    // public function withError($error)
    // {
    //     $clone = clone $this;
    //     $clone->$error = $error;
    //     return $clone;
    // }

    // response interface
    public function getStatusCode()
    {
        return $this->status;
    }

    public function withStatus($code, $reasonPhrase = null)
    {
        $this->filterStatus($code, $reasonPhrase);

        $clone = clone $this;
        $clone->status = $code;
        $clone->reasonPhrase = $reasonPhrase;

        return $clone;
    }

    public function getReasonPhrase()
    {
        if (null !== $this->reasonPhrase) {
            return $this->reasonPhrase;
        }
        return static::$messages[$this->status];
    }

    protected function filterStatus($code, &$reasonPhrase)
    {
        if (!is_integer($code)) {
            throw new BonoException('Invalid HTTP status code');
        }

        $reasonPhrase = $reasonPhrase ?: (isset(static::$messages[$code]) ? static::$messages[$code] : '');

        if (!is_string($reasonPhrase)) {
            throw new BonoException('ReasonPhrase must be a string');
        }
    }

    public function withCookie(
        $name,
        $value = '',
        $expire = 0,
        $path = '',
        $domain = '',
        $secure = false,
        $httponly = false
    ) {
        $this->cookies->set($name, $value, $expire, $path, $domain, $secure, $httponly);
        return $this;
    }

    public function withoutCookie($name, $path = '', $domain = '', $secure = false, $httponly = false)
    {
        $this->cookies->remove($name, $path, $domain, $secure, $httponly);
        return $this;
    }
}
