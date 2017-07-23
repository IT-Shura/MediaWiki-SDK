<?php

namespace Tests\MediaWiki\Project;

use MediaWiki\Project\Project;
use MediaWiki\Project\ProjectFactory;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;
use Mockery;
use Tests\TestCase;

class ProjectFactoryTest extends TestCase
{
    public function testCreateProjectWithoutApiUrls()
    {
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $projectFactory = new ProjectFactory($httpClient, $storage);

        $project = $projectFactory->createProject();

        $this->assertInstanceOf(Project::class, $project);
    }

    public function testCreateProjectWithApiUrls()
    {
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $apiUrl = 'https://wikipedia.org/w/api.php';
        $key = sprintf('%s.cookies', $apiUrl);

        $storage->shouldReceive('get')->once()->with($key, [])->andReturn([]);

        $projectFactory = new ProjectFactory($httpClient, $storage);

        $project = $projectFactory->createProject([
            'en' => $apiUrl,
        ]);

        $this->assertInstanceOf(Project::class, $project);
    }

    public function testHttpClientIsNotSameForCreatedProjects()
    {
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $httpClient = new \MediaWiki\HttpClient\CurlHttpClient();
        $storage = Mockery::mock(StorageInterface::class);

        $storage->shouldReceive('get')->times(4)->andReturn([]);

        $apiUrls = [
            'en' => 'https://en.wikipedia.org/w/api.php',
            'ru' => 'https://ru.wikipedia.org/w/api.php',
        ];

        $projectFactory = new ProjectFactory($httpClient, $storage);

        $project1 = $projectFactory->createProject($apiUrls);
        $project2 = $projectFactory->createProject($apiUrls);

        $httpClient1 = $project1->getApiCollection()->get('en')->getHttpClient();
        $httpClient2 = $project2->getApiCollection()->get('en')->getHttpClient();

        $this->assertFalse($httpClient1 === $httpClient2);
    }

    public function testStorageIsNotSameForCreatedProjects()
    {
        $httpClient = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $storage->shouldReceive('get')->times(4)->andReturn([]);

        $apiUrls = [
            'en' => 'https://en.wikipedia.org/w/api.php',
            'ru' => 'https://ru.wikipedia.org/w/api.php',
        ];

        $projectFactory = new ProjectFactory($httpClient, $storage);

        $project1 = $projectFactory->createProject($apiUrls);
        $project2 = $projectFactory->createProject($apiUrls);

        $storage1 = $project1->getApiCollection()->get('en')->getStorage();
        $storage2 = $project2->getApiCollection()->get('en')->getStorage();

        $this->assertFalse($storage1 === $storage2);
    }
}