<?php

namespace MediaWiki\Project;

use MediaWiki\Api\ApiCollection;
use MediaWiki\Api\ApiInterface;
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
    public function __construct(ApiCollection $api, ServiceManager $services)
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
     * @param string
     *
     * @return Project
     */
    public function setDefaultLanguage($language)
    {
        $this->defaultLanguage = $language;

        return $this;
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
     * @param ApiInterface $api
     */
    public function addApi($language, ApiInterface $api)
    {
        $this->api->add($language, $api);
    }

    /**
     * @param string|null $language
     *
     * @return ApiInterface
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
     * @return \MediaWiki\Services\Service
     */
    public function service($name)
    {
        return $this->services->getService($name);
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
     * @param ServiceManager $services
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
     * @return array
     */
    public static function getApiUrls()
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getApiUsernames()
    {
        return [];
    }
}
