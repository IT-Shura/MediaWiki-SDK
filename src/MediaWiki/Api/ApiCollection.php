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
     *
     * @throws InvalidArgumentException if API collection is not array
     */
    public function __construct($api = [])
    {
        if (!is_array($api)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be array, %s given', __METHOD__, gettype($api)));
        }

        foreach ($api as $language => $instance) {
            $this->add($language, $instance);
        }
    }

    /**
     * @param string $language
     * @param Api $api
     *
     * @throws InvalidArgumentException if language code is not string
     */
    public function add($language, Api $api)
    {
        if (!is_string($language)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($language)));
        }

        $this->api[$language] = $api;
    }

    /**
     * @param string $language
     * 
     * @return Api
     *
     * @throws InvalidArgumentException if language code is not string
     * @throws InvalidArgumentException if API wih specified language code does not exist
     */
    public function get($language)
    {
        if (!is_string($language)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($language)));
        }

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
     *
     * @throws InvalidArgumentException if language code is not string
     */
    public function has($language)
    {
        if (!is_string($language)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($language)));
        }

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
     * Enables query logging for all APIs.
     *
     * @return ApiCollection
     */
    public function enableQueryLog()
    {
        foreach ($this->api as $language => $api) {
            $api->enableQueryLog();
        }

        return $this;
    }

    /**
     * Disables query logging for all APIs.
     *
     * @return ApiCollection
     */
    public function disableQueryLog()
    {
        foreach ($this->api as $language => $api) {
            $api->disableQueryLog();
        }

        return $this;
    }

    /**
     * Returns query logs from all APIs.
     *
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
