<?php

namespace Tests\MediaWiki\Bot;

use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use MediaWiki\Bot\ProjectManager;
use MediaWiki\Bot\Project;
use org\bovigo\vfs\vfsStream;
use Tests\TestCase;
use Mockery;

/**
 * TODO: write tests createProjectMethod
 */
class ProjectManagerTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $projectsFolder;

    public function setUp()
    {
        $this->projectsFolder = vfsStream::setup('projects');
    }

    public function testSetGetNamespace()
    {
        $projectManager = $this->createProjectManager();

        // returns $this
        $this->assertEquals($projectManager, $projectManager->setNamespace('MyNamespace'));
        $this->assertEquals('MyNamespace', $projectManager->getNamespace());
    }

    public function testProjectExists()
    {
        $projectManager = $this->createProjectManager();

        $this->assertFalse($projectManager->projectExists('foo'));

        vfsStream::newFile('foo.php')->at($this->projectsFolder);

        $this->assertTrue($projectManager->projectExists('foo'));
    }

    /**
     * @expectedException RuntimeException
     */
    public function testLoadNotExistenProject()
    {
        $projectManager = $this->createProjectManager();

        $projectManager->loadProject('foo');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadProject()
    {
        $projectManager = $this->createProjectManager();

        $content = file_get_contents(__DIR__.'/../../Stubs/ProjectExample.php');

        vfsStream::newFile('project-example.php')->at($this->projectsFolder)->withContent($content);

        $projectManager->setNamespace('Tests\Stubs');

        $project = $projectManager->loadProject('project-example');

        $this->assertInstanceOf(Project::class, $project);
    }

    public function testProjectsFolder()
    {
        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $projectsFolder = vfsStream::url('projects');

        $projectManager = new ProjectManager($client, $storage, $projectsFolder);

        $this->assertEquals($projectsFolder, $projectManager->getProjectsFolder());
    }

    public function testGetHttpClient()
    {
        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $projectsFolder = vfsStream::url('projects');

        $projectManager = new ProjectManager($client, $storage, $projectsFolder);

        $this->assertEquals($client, $projectManager->getHttpClient());
    }

    public function testStorage()
    {
        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $projectsFolder = vfsStream::url('projects');

        $projectManager = new ProjectManager($client, $storage, $projectsFolder);

        $this->assertEquals($storage, $projectManager->getStorage());
    }

    protected function createProjectManager()
    {
        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $storage->shouldReceive('get');

        $projectsFolder = vfsStream::url('projects');

        return new ProjectManager($client, $storage, $projectsFolder);
    }
}
