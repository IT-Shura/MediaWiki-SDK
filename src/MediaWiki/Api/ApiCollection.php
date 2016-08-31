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
     * @param string $language
     * 
     * @return bool
     */
    public function has($language)
    {
        return array_key_exists($language, $this->api);
    }
}
