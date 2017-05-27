<?php

namespace MediaWiki\Bot;

use MediaWiki\Helpers;
use MediaWiki\Storage\StorageInterface;
use InvalidArgumentException;
use RuntimeException;

class CommandManager
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $commandsFolder;

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param string $commandsFolder
     */
    public function __construct(StorageInterface $storage, $commandsFolder)
    {
        $this->storage = $storage;

        $this->setCommandsFolder($commandsFolder);
    }

    /**
     * @param string $commandsFolder
     *
     * @throws InvalidArgumentException if path to command folder is not string
     */
    protected function setCommandsFolder($commandsFolder)
    {
        if (!is_string($commandsFolder)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($commandsFolder)));
        }

        $this->commandsFolder = $commandsFolder;
    }

    /**
     * @param string $namespace
     * 
     * @return CommandManager
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $name
     * @param Project $project
     * 
     * @return Command
     * 
     * @throws RuntimeException if project does not exists
     */
    public function getCommand($name, Project $project = null)
    {
        require_once $this->find($name);

        $class = sprintf('%s\%s', $this->namespace, Helpers\pascal_case($name));

        $command = new $class($this->storage, $project, $this);

        return $command;
    }

    /**
     * @return string[]
     */
    public function getCommandsList()
    {
        $files = scandir($this->commandsFolder);

        $commands = [];

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            $commands[] = basename($file, '.php');
        }

        return $commands;
    }

    /**
     * @return string
     */
    public function getCommandsFolder()
    {
        return $this->commandsFolder;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $name
     * 
     * @return string
     * 
     * @throws RuntimeException if project does not exists
     */
    protected function find($name)
    {
        $filename = sprintf('%s/%s.php', $this->commandsFolder, $name);

        if (file_exists($filename)) {
            return $filename;
        }

        $filename = sprintf('%s/%s/%s.php', $this->commandsFolder, $name, $name);

        if (file_exists($filename)) {
            return $filename;
        }

        throw new RuntimeException(sprintf('Command with name "%s" does not exists', $name));
    }
}
