<?php

namespace MediaWiki\Bot;

use MediaWiki\Helpers;
use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Services\ServiceManager;
use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use RuntimeException;

class ProjectManager
{
    /**
     * @var string
     */
    protected $projectsFolder;

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * Constructor.
     * 
     * @param string $projectsFolder
     */
    public function __construct(HttpClientInterface $client, StorageInterface $storage, $projectsFolder)
    {
        $this->projectsFolder = $projectsFolder;
        $this->client = $client;
        $this->storage = $storage;
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
        $filename = sprintf('%s/%s.php', $this->projectsFolder, $name);

        return file_exists($filename);
    }

    /**
     * @param string $name The name of the project
     * 
     * @return Project
     * 
     * @throws RuntimeException if project does not exists
     */
    public function loadProject($name)
    {
        $filename = sprintf('%s/%s.php', $this->projectsFolder, $name);

        if (!file_exists($filename)) {
            throw new RuntimeException(sprintf('Project with name "%s" does not exists', $name));
        }

        $apiCollection = new ApiCollection();
        $serviceManager = new ServiceManager($apiCollection);

        require_once $filename;

        $class = sprintf('%s\%s', $this->namespace, Helpers\pascal_case($name));

        $project = new $class($apiCollection, $serviceManager);

        foreach ($project->getApiUrls() as $language => $url) {
            $api = new Api($url, $this->client, $this->storage);

            $project->addApi($language, $api);
        }

        return $project;
    }

    /**
     * @return array
     */
    public function getProjectsList()
    {
        $files = scandir($this->projectsFolder);

        $projects = [];

        $apiCollection = new ApiCollection();

        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
                continue;
            }

            $data = require_once sprintf('%s/%s', $this->projectsFolder, $file);

            $class = Helpers\pascal_case(basename($file, '.php'));

            $projects[] = new $class($apiCollection);
        }

        return $projects;
    }

    /**
     * @return string
     */
    public function getProjectsFolder()
    {
        return $this->projectsFolder;
    }

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient()
    {
        return $this->client;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
