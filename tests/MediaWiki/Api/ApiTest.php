<?php

namespace Tests\MediaWiki;

use Tests\TestCase;
use MediaWiki\Api\Api;
use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use Mockery;

class ApiTest extends TestCase
{
    public function testConstructor()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $client, $storage);
    }

    public function testGetUrl()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $client, $storage);

        $this->assertEquals($url, $api->getUrl());
    }

    public function testGetClient()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $client, $storage);

        $this->assertEquals($client, $api->getClient());
    }

    public function testGetStorage()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $api = new Api($url, $client, $storage);

        $this->assertEquals($storage, $api->getStorage());
    }

    public function testQueryLog()
    {
        $method = 'GET';
        $url = 'http://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];
        $parameters = ['action' => 'query'];

        $expectedResponse = ['foo' => 'bar'];

        $headers = [];
        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);

        $expectedParameters = array_merge($defaultParameters, $parameters);

        $arguments = [$method, $url, $expectedParameters, $headers, $cookies];

        $client->shouldReceive('request')->times(3)->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $this->assertEquals([], $api->getQueryLog());

        $response = $api->request($method, $parameters);

        $this->assertEquals([], $api->getQueryLog());

        $api->enableQueryLog();

        $response = $api->request($method, $parameters);

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters,
                'headers' => $headers,
                'cookies' => $cookies,
            ]
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog());

        $api->disableQueryLog();

        $response = $api->request($method, $parameters);

        $this->assertEquals($expectedLog, $api->getQueryLog());
    }

    public function testRequest()
    {
        $method = 'GET';
        $url = 'http://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];
        $parameters = ['action' => 'query'];

        $expectedResponse = ['foo' => 'bar'];

        $headers = [];
        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);

        $expectedParameters = array_merge($defaultParameters, $parameters);

        $arguments = [$method, $url, $expectedParameters, $headers, $cookies];

        $client->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $response = $api->request($method, $parameters);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequestDecodeNotJson()
    {
        $method = 'GET';
        $url = 'http://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];
        $parameters = ['action' => 'query', 'format' => 'xml'];

        $expectedResponse = ['foo' => 'bar'];

        $headers = [];
        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $response = $api->request('GET', $parameters);
    }

    public function testLogin()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $username = 'foo';
        $password = 'bar';
        $domain = null;

        $headers = [];
        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);

        $expectedParameters = [
            'action' => 'login',
            'lgname' => $username,
            'lgpassword' => $password,
            'lgdomain' => $domain,
            'format' => 'json',
        ];

        $expectedResponse = [
            'login' => [
                'result' => 'NeedToken',
                'token' => 'token',
            ],
        ];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $client->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        // send received token
        $expectedParameters['lgtoken'] = 'token';

        $expectedResponse = [
            'login' => [
                'result' => 'Success',
            ],
        ];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $client->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $receivedCookies = [
            'foo' => 'bar',
        ];

        $client->shouldReceive('getCookies')->once()->andReturn($receivedCookies);

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);
        $storage->shouldReceive('forever')->once()->with($key, $receivedCookies)->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $result = $api->login($username, $password);

        $this->assertTrue($result);
    }
}
