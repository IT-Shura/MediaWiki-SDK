<?php

namespace MediaWiki\Bot;

use MediaWiki\Helpers;
use MediaWiki\Storage\StorageInterface;
use InvalidArgumentException;

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
    protected $commandNamespace;

    /**
     * Constructor.
     * 
     * @param StorageInterface $storage
     * @param string $commandsFolder
     * @param string $commandNamespace
     */
    public function __construct(StorageInterface $storage, $commandsFolder, $commandNamespace = '')
    {
        $this->storage = $storage;
        $this->commandsFolder = $commandsFolder;
        $this->commandNamespace = $commandNamespace;
    }

    /**
     * @param string $name
     * @param Project $project
     */
    public function getCommand($name, Project $project = null)
    {
        require_once $this->find($name);

        $class = sprintf('%s\%s', $this->commandNamespace, Helpers\pascal_case($name));

        $command = new $class($this->storage, $project, $this);

        return $command;
    }

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
     * @param string $name
     * 
     * @return string
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

        throw new InvalidArgumentException(sprintf('Command with name "%s" does not exists', $name));
    }
}
