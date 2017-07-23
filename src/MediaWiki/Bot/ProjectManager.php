<?php

namespace MediaWiki\Bot;

use MediaWiki\Helpers;
use MediaWiki\Project\Project;
use MediaWiki\Project\ProjectFactoryInterface;
use RuntimeException;

class ProjectManager
{
    /**
     * @var string
     */
    protected $projectFactory;

    /**
     * @var string
     */
    protected $projectsDirectory;

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * Constructor.
     *
     * @param ProjectFactoryInterface $projectFactory
     * @param string $projectsDirectory
     */
    public function __construct(ProjectFactoryInterface $projectFactory, $projectsDirectory)
    {
        $this->projectFactory = $projectFactory;
        $this->projectsDirectory = $projectsDirectory;
    }

    /**
     * @param string $namespace
     * 
     * @return ProjectManager
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
     * @param string $name The name of the project
     * 
     * @return bool
     */
    public function projectExists($name)
    {
        $filename = sprintf('%s/%s.php', $this->projectsDirectory, $name);

        return file_exists($filename);
    }

    /**
     * @param string $projectName
     * 
     * @return Project
     * 
     * @throws RuntimeException if project does not exists
     */
    public function loadProject($projectName)
    {
        $filename = sprintf('%s/%s.php', $this->projectsDirectory, $projectName);

        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('Project with name "%s" does not exist', $projectName));
        }

        require_once $filename;

        $projectClassName = sprintf('%s\%s', $this->namespace, Helpers\pascal_case($projectName));

        $apiUrls = call_user_func([$projectClassName, 'getApiUrls']);

        return $this->projectFactory->createProject($apiUrls, $projectClassName);
    }

    /**
     * @return array
     */
    public function getProjectsList()
    {
        $files = scandir($this->projectsDirectory);

        $projects = [];

        foreach ($files as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            require_once sprintf('%s/%s', $this->projectsDirectory, $filename);

            $projectClassName = Helpers\pascal_case(basename($filename, '.php'));

            $projects[] = $this->projectFactory->createProject($projectClassName);
        }

        return $projects;
    }

    /**
     * @return string
     */
    public function getProjectsDirectory()
    {
        return $this->projectsDirectory;
    }
}
