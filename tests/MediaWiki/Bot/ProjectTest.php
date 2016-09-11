<?php

namespace Tests\MediaWiki;

use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\Api;
use Tests\Stubs\ProjectExample;
use Tests\TestCase;
use Mockery;

class ProjectTest extends TestCase
{
    public function testGetName()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $this->assertEquals('foo', $project->getName());
    }

    public function testGetTitle()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $this->assertEquals('Foo', $project->getTitle());
    }

    public function testGetDefaultLanguage()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $this->assertEquals('en', $project->getDefaultLanguage());
    }

    public function testGetApiCollection()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $this->assertEquals($apiCollection, $project->getApiCollection());
    }

    public function testAddApi()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $enApi = $this->createApi();
        $ruApi = $this->createApi();

        $project->addApi('en', $enApi);
        $project->addApi('ru', $ruApi);

        $this->assertEquals($enApi, $project->getApiCollection()->get('en'));
        $this->assertEquals($ruApi, $project->getApiCollection()->get('ru'));
    }

    public function testApi()
    {
        $enApi = $this->createApi();
        $ruApi = $this->createApi();

        $apiCollection = new ApiCollection();

        $apiCollection->add('en', $enApi);
        $apiCollection->add('ru', $ruApi);

        $project = new ProjectExample($apiCollection);

        $this->assertEquals($enApi, $project->api('en'));
        $this->assertEquals($ruApi, $project->api('ru'));
    }

    public function testGetApiUrls()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $expectation = [
            'en' => 'http://en.wikipedia.org/w/api.php',
            'ru' => 'http://ru.wikipedia.org/w/api.php',
        ];

        $this->assertEquals($expectation, $project->getApiUrls());
    }

    public function testGetApiUsernames()
    {
        $apiCollection = new ApiCollection();
        $project = new ProjectExample($apiCollection);

        $expectation = [
            'en' => 'FooBot',
            'ru' => 'FooBot',
        ];

        $this->assertEquals($expectation, $project->getApiUsernames());
    }

    protected function createApi()
    {
        $url = 'http://wikipedia.org/w/api.php';

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $key = sprintf('%s.cookies', $url);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        return new Api($url, $client, $storage);
    }
}
