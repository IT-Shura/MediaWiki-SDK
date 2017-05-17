<?php

namespace MediaWiki\Services;

use MediaWiki\Api\ApiCollection;
use RuntimeException;

class ServiceManager
{
    /**
     * @var array
     */
    protected $services = [
        'namespaces' => Namespaces::class,
        'pages' => Pages::class,
    ];

    /**
     * @var Service[]
     */
    protected $instances = [];

    /**
     * @var ApiCollection
     */
    protected $api;

    /**
     * Constructor.
     *
     * @param ApiCollection $api
     */
    public function __construct(ApiCollection $api)
    {
        $this->api = $api;
    }

    /**
     * @param string $name
     *
     * @return Service
     */
    public function getService($name)
    {
        if (!array_key_exists($name, $this->services)) {
            throw new RuntimeException(sprintf('Service %s does not exists', $name));
        }

        if (!array_key_exists($name, $this->instances)) {
            $class = $this->services[$name];

            $this->instances[$name] = new $class($this->api);
        }

        return $this->instances[$name];
    }
}
