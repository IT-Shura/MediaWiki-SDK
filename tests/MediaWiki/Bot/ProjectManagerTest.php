<?php

namespace Tests\MediaWiki\Bot;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Bot\ProjectManager;
use MediaWiki\Project\ProjectFactoryInterface;
use MediaWiki\Services\ServiceManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Tests\Stubs\ProjectExample;
use Tests\TestCase;
use Mockery;

class ProjectManagerTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $projectsDirectory;

    /**
     * @var string
     */
    private $projectsDirectoryPath;

    public function setUp()
    {
        $this->projectsDirectory = vfsStream::setup('projects');
        $this->projectsDirectoryPath = vfsStream::url('projects');
    }

    public function testSetGetNamespace()
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        // returns $this
        $this->assertEquals($projectManager, $projectManager->setNamespace('MyNamespace'));
        $this->assertEquals('MyNamespace', $projectManager->getNamespace());
    }

    public function testProjectExists()
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $this->assertFalse($projectManager->projectExists('foo'));

        vfsStream::newFile('foo.php')->at($this->projectsDirectory);

        $this->assertTrue($projectManager->projectExists('foo'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadNotExistingProject()
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $projectManager->loadProject('foo');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLoadProject()
    {
        $content = file_get_contents(__DIR__.'/../../Stubs/ProjectExample.php');

        vfsStream::newFile('project-example.php')->at($this->projectsDirectory)->withContent($content);

        require_once $this->projectsDirectoryPath.'/project-example.php';

        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        $project = new ProjectExample($apiCollection, $serviceManager);

        $projectFactory->shouldReceive('createProject')->with(
            ProjectExample::getApiUrls(),
            'Tests\Stubs\ProjectExample'
        )->once()->andReturn($project);

        $projectsFolder = vfsStream::url('projects');

        $projectManager = new ProjectManager($projectFactory, $projectsFolder);

        $projectManager->setNamespace('Tests\Stubs');

        $loadedProject = $projectManager->loadProject('project-example');

        $this->assertEquals($project, $loadedProject);
    }

    public function testProjectsFolder()
    {
        $projectFactory = Mockery::mock(ProjectFactoryInterface::class);

        $projectManager = new ProjectManager($projectFactory, $this->projectsDirectoryPath);

        $this->assertEquals($this->projectsDirectoryPath, $projectManager->getProjectsDirectory());
    }
}
