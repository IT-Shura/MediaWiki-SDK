<?php

namespace MediaWiki\Api;

use LogicException;
use MediaWiki\Api\Exceptions\ApiException;
use MediaWiki\HttpClient\HttpClientInterface;
use MediaWiki\Storage\StorageInterface;

interface ApiInterface
{
    /**
     * @return string
     */
    public function getUrl();

    /**
     * @return HttpClientInterface
     */
    public function getHttpClient();

    /**
     * @return StorageInterface
     */
    public function getStorage();

    /**
     * Enables query logging.
     */
    public function enableQueryLog();

    /**
     * Disables query logging.
     */
    public function disableQueryLog();

    /**
     * Returns query log.
     *
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getQueryLog($fields = null, $count = null);

    /**
     * @param string $method HTTP method name
     * @param array|string $parameters
     * @param array $headers
     * @param bool $decode
     *
     * @return string|array
     *
     * @throws LogicException if request method is not allowed
     * @throws LogicException if response decoding enabled and response type is not JSON
     */
    public function request($method, $parameters = [], $headers = [], $decode = true);

    /**
     * @param string $method
     *
     * @return bool
     */
    public function isMethodAllowed($method);

    /**
     * @return array
     */
    public function getAllowedRequestMethods();

    /**
     * @param array $parameters
     * @param bool $decode
     *
     * @return array|string
     *
     * @throws LogicException if action specified and not equals "query"
     */
    public function query($parameters, $decode = true);

    /**
     * @param array $parameters
     *
     * @return Api
     */
    public function setDefaultParameters(array $parameters);

    /**
     * @return array
     */
    public function getDefaultParameters();

    /**
     * @param string $username
     * @param string $password
     * @param string|null $domain
     *
     * @throws ApiException
     */
    public function login($username, $password, $domain = null);

    /**
     * @return bool
     */
    public function isLoggedIn();

    /**
     * @return bool
     */
    public function logout();
}