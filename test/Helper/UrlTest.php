<?php
namespace Bono\Test\Helper;

use PHPUnit_Framework_TestCase;
use Bono\Helper\Url;

class UrlTest extends PHPUnit_Framework_TestCase
{
    public function testGenerateBundleUrl()
    {
        $url = Url::bundle('/');
        $this->assertEquals($url, '');

        $url = Url::bundle('/foo');
        $this->assertEquals($url, '/foo');
    }

    public function testGenerateAssetUrl()
    {
        $url = Url::asset('/foo.js');
        $this->assertEquals($url, '/foo.js');
    }

    public function testFormatUrl()
    {
        $url = Url::format('/foo/{id}/bar', [ 'id' => 3333 ]);
        $this->assertEquals($url, '/foo/3333/bar');
    }
}