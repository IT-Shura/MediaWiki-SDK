<?php

namespace MediaWiki\Services;

use MediaWiki\Api\ApiCollection;

class Service
{
    /**
     * Constructor.
     * 
     * @param ApiCollection $api
     */
    public function __construct(ApiCollection $api)
    {
        $this->api = $api;
    }

    protected function api($language)
    {
        return $this->api->get($language);
    }
}
