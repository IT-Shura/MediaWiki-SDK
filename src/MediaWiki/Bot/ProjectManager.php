<?php

namespace MediaWiki\Bot;

use MediaWiki\Helpers;
use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Services\ServiceManager;
use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use RuntimeException;
use InvalidArgumentException;

class ProjectManager
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var StorageInterface
     */
    protected $storage;

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
     * @param HttpClientInterface $client
     * @param StorageInterface $storage
     * @param string $projectsFolder
     */
    public function __construct(HttpClientInterface $client, StorageInterface $storage, $projectsFolder = null)
    {
        $this->client = $client;
        $this->storage = $storage;
        $this->projectsFolder = $projectsFolder;
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

        require_once $filename;

        $class = sprintf('%s\%s', $this->namespace, Helpers\pascal_case($name));

        return $this->createProject([], [], $class);
    }

    /**
     * @param  array  $apiUrls
     * @param  array  $apiUsernames
     * @param  Project|string $class
     * 
     * @return Project
     */
    public function createProject($apiUrls = [], $apiUsernames = [], $class = Project::class)
    {
        if (is_string($class)) {
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Class "%s" does not exists', $class));
            }

            $project = new $class();
        } else {
            $project = $class;
        }

        if (!$project instanceof Project) {
            throw new InvalidArgumentException(sprintf('Argument 1 passed to %s must be an instance of %s, %s given', __METHOD__, Project::class, (is_object($project) ? get_class($project) : gettype($project))));
        }

        if (!is_array($apiUrls)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 2 to be array, %s given', __METHOD__, gettype($apiUrls)));
        }

        if (!is_array($apiUsernames)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 3 to be array, %s given', __METHOD__, gettype($apiUsernames)));
        }

        if (!empty($apiUrls)) {
            foreach ($apiUrls as $language => $url) {
                $project->setApiUrl($language, $url);
            }
        }

        if (!empty($apiUsernames)) {
            foreach ($apiUsernames as $language => $username) {
                $project->setApiUsername($language, $username);
            }
        }

        $apiCollection = new ApiCollection();

        foreach ($project->getApiUrls() as $language => $url) {
            $api = new Api($url, $this->client, $this->storage);

            $apiCollection->add($language, $api);
        }

        $serviceManager = new ServiceManager($apiCollection);

        $project->setApiCollection($apiCollection);
        $project->setServiceManager($serviceManager);

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
