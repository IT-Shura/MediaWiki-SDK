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
     * @var array
     */
    protected $apiUsernames = [];

    /**
     * @var array
     */
    protected $apiUrls = [];

    /**
     * Constructor.
     * 
     * @param ApiCollection $api
     * @param ServiceManager $services
     */
    public function __construct(ApiCollection $api = null, ServiceManager $services = null)
    {
        $this->api = $api === null ? new ApiCollection() : $api;

        $this->services = $services === null ? new ServiceManager($this->api) : $services;
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
     * @param ApiCollection $api
     * 
     * @return Project
     */
    public function setApiCollection(ApiCollection $api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * @return ApiCollection
     */
    public function getApiCollection()
    {
        return $this->api;
    }

    /**
     * @param ServiceManager $api
     * 
     * @return Project
     */
    public function setServiceManager(ServiceManager $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->services;
    }

    /**
     * @param string $language
     * @param string $url
     */
    public function setApiUrl($language, $url)
    {
        $this->apiUrls[$language] = $url;
    }

    /**
     * @return array
     */
    public function getApiUrls()
    {
        return $this->apiUrls;
    }

    /**
     * @param string $language
     * @param string $username
     */
    public function setApiUsername($language, $username)
    {
        $this->apiUsernames[$language] = $username;
    }

    /**
     * @return array
     */
    public function getApiUsernames()
    {
        return $this->apiUsernames;
    }
}
