<?php

namespace MediaWiki\Api;

use InvalidArgumentException;

class ApiCollection
{
    /**
     * @var array
     */
    protected $api = [];

    /**
     * Constructor.
     * 
     * @param array $api
     */
    public function __construct($api = [])
    {
        foreach ($api as $language => $instance) {
            $this->add($language, $instance);
        }
    }

    /**
     * @param string $language
     * @param Api    $api
     */
    public function add($language, Api $api)
    {
        $this->api[$language] = $api;
    }

    /**
     * @param string $language
     * 
     * @return Api
     */
    public function get($language)
    {
        if ($this->has($language)) {
            return $this->api[$language];
        }

        throw new InvalidArgumentException(sprintf('API with code "%s" not found', $language));
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->api;
    }

    /**
     * @param string $language
     * 
     * @return bool
     */
    public function has($language)
    {
        return array_key_exists($language, $this->api);
    }

    /**
     * @return string[]
     */
    public function getLanguages()
    {
        return array_keys($this->api);
    }

    /**
     * @return Project
     */
    public function enableQueryLog()
    {
        foreach ($this->api as $language => $api) {
            $api->enableQueryLog();
        }

        return $this;
    }

    /**
     * @return Project
     */
    public function disableQueryLog()
    {
        foreach ($this->api as $language => $api) {
            $api->disableQueryLog();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getQueryLog()
    {
        $log = [];

        foreach ($this->api as $language => $api) {
            $log[$language] = $api->getQueryLog();
        }

        return $log;
    }
}
