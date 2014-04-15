<?php

namespace TestCase;

class GetMethodTest extends \TestCase\BonoTestCase
{
    public function testIndex()
    {
        $this->get('/');
        $this->assertEquals('200', $this->response->status());
    }

    public function testUserPage()
    {
        $this->get('/user');
        $this->assertEquals('200', $this->response->status());
    }

    public function testJsonView()
    {
        $this->get('/user.json');
        $this->assertEquals('200', $this->response->status());
        $this->assertEquals('application/json', $this->app->response->headers->get('Content-Type'));
    }

    public function testError()
    {
        $this->get('/user/xxx.json');
        $this->assertEquals('500', $this->response->status());
    }

    public function testNotFound()
    {
        $this->get('/xxx');
        $this->assertEquals('404', $this->response->status());
    }
}
