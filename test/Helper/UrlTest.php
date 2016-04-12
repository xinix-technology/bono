<?php
namespace Bono\Test\Helper;

use PHPUnit_Framework_TestCase;
use Bono\Http\Uri;
use Bono\Helper\Url;

class UrlTest extends PHPUnit_Framework_TestCase
{
    protected $uri;

    public function setUp()
    {
        $this->uri = new Uri();
    }
    public function testGenerateBundleUrl()
    {
        $url = Url::bundle('/', $this->uri);
        $this->assertEquals($url, 'http://127.0.0.1:80');

        $url = Url::bundle('/foo', $this->uri);
        $this->assertEquals($url, 'http://127.0.0.1:80/foo');
    }

    public function testGenerateAssetUrl()
    {
        $url = Url::asset('/foo.js', $this->uri);
        $this->assertEquals($url, 'http://127.0.0.1:80/foo.js');
    }

    public function testFormatUrl()
    {
        $url = Url::format('/foo/{id}/bar', [ 'id' => 3333 ]);
        $this->assertEquals($url, '/foo/3333/bar');
    }
}