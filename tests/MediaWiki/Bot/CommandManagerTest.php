<?php

namespace Tests\MediaWiki\Bot;

use InvalidArgumentException;
use MediaWiki\Storage\StorageInterface;
use MediaWiki\Bot\CommandManager;
use MediaWiki\Bot\Command;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use RuntimeException;
use Tests\TestCase;
use Mockery;

class CommandManagerTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $commandsFolder;

    public function setUp()
    {
        $this->commandsFolder = vfsStream::setup('commands');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructWithInvalidCommandsFolder()
    {
        $storage = Mockery::mock(StorageInterface::class);

        $commandManager = new CommandManager($storage, null);
    }

    public function testSetGetNamespace()
    {
        $commandManager = $this->createCommandManager();

        // returns $this
        $this->assertEquals($commandManager, $commandManager->setNamespace('MyNamespace'));
        $this->assertEquals('MyNamespace', $commandManager->getNamespace());
    }

    /**
     * @expectedException RuntimeException
     */
    public function testGetNotExistenCommand()
    {
        $commandManager = $this->createCommandManager();

        $commandManager->getCommand('foo');
    }

    /**
     * TODO:
     * 
     * - add test with separate command folder
     * - add test with project
     * 
     * @runInSeparateProcess
     */
    public function testGetCommand()
    {
        $commandManager = $this->createCommandManager();

        $content = file_get_contents(__DIR__.'/../../Stubs/CommandExample.php');

        vfsStream::newFile('command-example.php')->at($this->commandsFolder)->withContent($content);

        $commandManager->setNamespace('Tests\Stubs');

        $command = $commandManager->getCommand('command-example');

        $this->assertInstanceOf(Command::class, $command);
    }

    public function testGetCommandsList()
    {
        $commandManager = $this->createCommandManager();

        $this->assertEquals([], $commandManager->getCommandsList());

        vfsStream::create(['foo.php' => '', 'bar.php' => '', 'baz' => ['baz.php' => '']], $this->commandsFolder);

        $this->assertEquals(['bar', 'baz', 'foo'], $commandManager->getCommandsList());
    }

    public function testCommandsFolder()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        $commandManager = new CommandManager($storage, $commandsFolder);

        $this->assertEquals($commandsFolder, $commandManager->getCommandsDirectory());
    }

    public function testStorage()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        $commandManager = new CommandManager($storage, $commandsFolder);

        $this->assertEquals($storage, $commandManager->getStorage());
    }

    protected function createCommandManager()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $commandsFolder = vfsStream::url('commands');

        return new CommandManager($storage, $commandsFolder);
    }
}
