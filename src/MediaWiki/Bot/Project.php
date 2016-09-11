<?php

namespace MediaWiki\Bot;

use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use MediaWiki\Services\ServiceManager;
use LogicException;

class Project
{
    /**
     * @var ApiCollection
     */
    protected $api;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $defaultLanguage;

    /**
     * Constructor.
     * 
     * @param ApiCollection $api
     * @param ServiceManager $services
     */
    public function __construct(ApiCollection $api, ServiceManager $services = null)
    {
        $this->api = $api;
        $this->services = $services;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDefaultLanguage()
    {
        return $this->defaultLanguage;
    }

    /**
     * @param string $language
     * @param Api    $api
     */
    public function addApi($language, Api $api)
    {
        $this->api->add($language, $api);
    }

    /**
     * @param string|null $language
     * 
     * @return Mediawiki\Api\Api
     */
    public function api($language = null)
    {
        if ($language === null and $this->defaultLanguage === null) {
            throw new LogicException('Please, specify language of API or default language of project');
        }

        $language = $language === null ? $this->defaultLanguage : $language;

        return $this->api->get($language);
    }

    /**
     * @param string $name
     * 
     * @return MediaWiki\Services\Service
     */
    public function service($name)
    {
        return $this->services->get($name);
    }

    /**
     * @return ApiCollection
     */
    public function getApiCollection()
    {
        return $this->api;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->services;
    }

    /**
     * @return array
     */
    public function getApiUrls()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getApiUsernames()
    {
        return [];
    }
}
