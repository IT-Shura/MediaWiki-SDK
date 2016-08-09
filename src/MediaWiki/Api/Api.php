<?php

namespace MediaWiki\Api;

use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use MediaWiki\Api\Exceptions\ApiException;
use MediaWiki\Api\Exceptions\AccessDeniedException;
use InvalidArgumentException;
use RuntimeException;

class Api
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var Mediawiki\HttpClient\HttpClientInterface
     */
    protected $client;

    /**
     * @var Mediawiki\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array
     */
    protected $cookies;

    /**
     * @var string
     */
    private $version;

    /**
     * Constructor.
     * 
     * @param string $url
     * @param HttpClientInterface $client
     * @param StorageInterface $storage
     */
    public function __construct($url, HttpClientInterface $client, StorageInterface $storage)
    {
        $this->setUrl($url);

        $this->client = $client;
        $this->storage = $storage;

        $key = sprintf('%s.cookies', $this->url);

        $this->cookies = $this->storage->get($key, []);
    }

    /**
     * @param string $url
     */
    protected function setUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($url)));
        }

        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return Mediawiki\HttpClient\HttpClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Mediawiki\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param strting $method HTTP method name
     * @param array   $parameters
     * @param array   $headers
     * @param bool    $decode
     * 
     * @return string|array
     */
    public function request($method, $parameters = [], $headers = [], $decode = true)
    {
        $parameters = array_merge($this->getDefaultParameters(), $parameters);

        $response = $this->client->request($method, $this->url, $parameters, $headers, $this->cookies);

        if ($decode) {
            $response = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException(sprintf('API response is not valid JSON (%s)', $this->url));
            }

            if (array_key_exists('error', $response)) {
                $error = $response['error'];

                if ($error['code'] === 'readapidenied') {
                    throw new AccessDeniedException($error['info'], $error['code']);
                }
            }
        }

        return $response;
    }

    /**
     * @return array
     */
    protected function getDefaultParameters()
    {
        return ['format' => 'json'];
    }

    /**
     * @param string $username
     * @param string $password
     * @param string|null $domain
     * 
     * @return bool
     * 
     * @throws ApiException
     */
    public function login($username, $password, $domain = null)
    {
        $data = [
            'action' => 'login',
            'lgname' => $username,
            'lgpassword' => $password,
            'lgdomain' => $domain,
        ];

        $response = $this->request('POST', $data);

        if ($response['login']['result'] === 'NeedToken') {
            $data['lgtoken'] = $response['login']['token'];

            $response = $this->request('POST', $data);
        }

        if ($response['login']['result'] === 'Success') {
            $this->cookies = $this->client->getCookies();

            $key = sprintf('%s.cookies', $this->url);

            $this->storage->forever($key, $this->cookies);

            return true;
        }

        throw new ApiException($response['login']['result']);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($this->cookies !== []) {
            return true;
        }
    }

    /**
     * @return bool
     */
    public function logout()
    {
        $this->cookies = [];

        $key = sprintf('%s.cookies', $this->url);

        $this->storage->forget($key);

        $data = [
            'action' => 'logout',
        ];

        $response = $this->request('POST', $data);

        return $response === [];
    }

    /**
     * @param  array $parameters
     * 
     * @return array
     */
    public function query($parameters, $decode = true)
    {
        $parameters = array_merge($parameters, ['action' => 'query']);

        return $this->request('POST', $parameters, [], $decode);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $response = $this->request('POST', [
                'action' => 'query',
                'meta' => 'siteinfo',
                'continue' => '',
            ]);

            $segments = explode(' ', $response['query']['general']['generator']);

            $this->version = $segments[1];
        }

        return $this->version;
    }
}
