<?php

namespace Tests\MediaWiki\Project;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\Api;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Services\ServiceManager;
use MediaWiki\Storage\StorageInterface;
use Tests\Stubs\ProjectExample;
use Tests\TestCase;
use Mockery;

class ProjectTest extends TestCase
{
    public function testGetName()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $this->assertEquals('foo', $project->getName());
    }

    public function testGetTitle()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $this->assertEquals('Foo', $project->getTitle());
    }

    public function testGetDefaultLanguage()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $this->assertEquals('en', $project->getDefaultLanguage());
    }

    public function testGetApiCollection()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $this->assertEquals($apiCollection, $project->getApiCollection());
    }

    public function testAddApi()
    {
        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

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

        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $this->assertEquals($enApi, $project->api('en'));
        $this->assertEquals($ruApi, $project->api('ru'));
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
