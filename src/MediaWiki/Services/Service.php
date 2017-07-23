<?php

namespace MediaWiki\Services;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\ApiInterface;

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

    /**
     * @param string $language
     *
     * @return ApiInterface
     */
    protected function api($language)
    {
        return $this->api->get($language);
    }
}
