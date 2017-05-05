<?php

namespace Tests\MediaWiki\Api;

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

    public function testQueryLogging()
    {
        $method = 'GET';
        $url = 'http://wikipedia.org/w/api.php';

        $defaultParameters = ['format' => 'json'];

        $parameters1 = ['action' => 'query'];
        $parameters2 = ['action' => 'query'];

        $expectedParameters1 = array_merge($defaultParameters, $parameters1);
        $expectedParameters2 = array_merge($defaultParameters, $parameters1);

        $expectedResponse1 = ['foo' => 'bar'];
        $expectedResponse2 = ['baz' => 'qux'];

        $headers = [];
        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);

        $arguments1 = [$method, $url, $expectedParameters1, $headers, $cookies];
        $arguments2 = [$method, $url, $expectedParameters2, $headers, $cookies];

        $client->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));
        $client->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));
        $client->shouldReceive('request')->once()->withArgs($arguments2)->andReturn(json_encode($expectedResponse2));
        $client->shouldReceive('request')->once()->withArgs($arguments1)->andReturn(json_encode($expectedResponse1));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $this->assertEquals([], $api->getQueryLog());

        $api->request($method, $parameters1);

        $this->assertEquals([], $api->getQueryLog());

        $api->enableQueryLog();

        $api->request($method, $parameters1);
        $api->request($method, $parameters2);

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters1,
                'response' => $expectedResponse1,
            ],
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog());

        $api->disableQueryLog();

        $api->request($method, $parameters1);

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters1,
                'headers' => $headers,
                'cookies' => $cookies,
                'response' => $expectedResponse1,
            ],
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'headers' => $headers,
                'cookies' => $cookies,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(['method', 'parameters', 'headers', 'cookies', 'response']));

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(null, 1));

        $expectedLog = [
            [
                'method' => $method,
                'parameters' => $expectedParameters2,
                'response' => $expectedResponse2,
            ],
        ];

        $this->assertEquals($expectedLog, $api->getQueryLog(['method', 'parameters', 'response'], 1));
    }

    /**
     * TODO:
     * - test method with string query (foo=bar&baz=qux)
     */
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
     * @expectedException LogicException
     */
    public function testRequestWithNotAllowedMethod()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $parameters = ['action' => 'query'];

        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $api->request('PUT', $parameters);
    }

    /**
     * @expectedException LogicException
     */
    public function testRequestDecodeNotJson()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $parameters = ['action' => 'query', 'format' => 'xml'];

        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $api->request('GET', $parameters);
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

    /**
     * TODO:
     * - test method with string query (foo=bar&baz=qux)
     */
    public function testQuery()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $headers = [];
        $cookies = [];

        $expectedParameters = [
            'action' => 'query',
            'format' => 'json',
            'titles' => 'Foo'
        ];

        $expectedResponse = ['response' => 'Bar'];

        $arguments = ['POST', $url, $expectedParameters, $headers, $cookies];

        $client = Mockery::mock(HttpClientInterface::class);

        $client->shouldReceive('request')->once()->withArgs($arguments)->andReturn(json_encode($expectedResponse));

        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $api->query(['titles' => 'Foo']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testQueryWithInvalidAction()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $cookies = [];

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn($cookies);

        $api = new Api($url, $client, $storage);

        $api->query(['action' => 'parse']);
    }
}
