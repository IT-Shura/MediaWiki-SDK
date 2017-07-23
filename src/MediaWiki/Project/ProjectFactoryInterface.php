<?php

namespace MediaWiki\Project;

use InvalidArgumentException;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;
use RuntimeException;

interface ProjectFactoryInterface
{
    /**
     * Constructor.
     *
     * @param HttpClientInterface $httpClient
     * @param StorageInterface $storage
     */
    public function __construct(HttpClientInterface $httpClient, StorageInterface $storage);

    /**
     * @param array $apiUrls
     * @param string $projectClassName
     *
     * @return Project
     *
     * @throws InvalidArgumentException if project class name is not string
     * @throws RuntimeException if specified project class does not exist
     * @throws RuntimeException if specified project class is not MediaWiki\Project\Project or a subclass of it
     */
    public function createProject(array $apiUrls = [], $projectClassName = Project::class);
}