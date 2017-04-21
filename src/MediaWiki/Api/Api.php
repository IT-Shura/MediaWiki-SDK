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
     * @var bool
     */
    protected $logQueries = false;

    /**
     * @var array
     */
    protected $queryLog = [];

    /**
     * @var array
     */
    protected $defaultParameters = [
        'format' => 'json',
    ];

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
     *
     * @throws InvalidArgumentException if API URL is not string
     * @throws RuntimeException if API address is not valid URL
     */
    protected function setUrl($url)
    {
        if (!is_string($url)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($url)));
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException(sprintf('API address must must be a valid URL (%s)', $url));
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
     * Enables query logging.
     */
    public function enableQueryLog()
    {
        $this->logQueries = true;
    }

    /**
     * Disables query logging.
     */
    public function disableQueryLog()
    {
        $this->logQueries = false;
    }

    /**
     * @return array
     */
    public function getQueryLog()
    {
        return $this->queryLog;
    }

    /**
     * @param strting $method HTTP method name
     * @param array|string $parameters
     * @param array $headers
     * @param bool $decode
     * 
     * @return string|array
     */
    public function request($method, $parameters = [], $headers = [], $decode = true)
    {
        if (is_string($parameters)) {
            parse_str($parameters, $result);

            $parameters = $result;
        }

        $parameters = array_merge($this->getDefaultParameters(), $parameters);

        if ($decode and (strtolower($parameters['format']) !== 'json')) {
            throw new InvalidArgumentException('Only JSON can be decoded. Specify JSON format or disable decoding');
        }

        if ($this->logQueries) {
            $this->queryLog[] = [
                'method' => $method,
                'parameters' => $parameters,
                'headers' => $headers,
                'cookies' => $this->cookies,
            ]; 
        }

        $response = $this->client->request($method, $this->url, $parameters, $headers, $this->cookies);

        if ($decode) {
            $response = $this->decodeResponse($response);
        }

        return $response;
    }

    /**
     * @param string $response
     *
     * @return array
     *
     * @throws RuntimeException if response is not valid JSON
     * @throws AccessDeniedException if access to API or section denied (e.g., unauthorized request)
     */
    protected function decodeResponse($response)
    {
        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(sprintf('API response is not valid JSON (%s)', $this->url));
        }

        if (array_key_exists('error', $decodedResponse)) {
            $error = $decodedResponse['error'];

            if ($error['code'] === 'readapidenied') {
                throw new AccessDeniedException($error['info'], $error['code']);
            }
        }

        return $decodedResponse;
    }

    /**
     * @param array $parameters
     * 
     * @return Api
     */
    public function setDefaultParameters($parameters)
    {
        if (!is_array($parameters)) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 1 to be string, %s given', __METHOD__, gettype($parameters)));
        }

        $this->defaultParameters = $parameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return $this->defaultParameters;
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

        return false;
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
     * @param array $parameters
     * @param bool $decode
     *
     * @return array|string
     */
    public function query($parameters, $decode = true)
    {
        if (is_string($parameters)) {
            parse_str($parameters, $result);

            $parameters = $result;
        }

        if (array_key_exists('action', $parameters) and strtolower($parameters['action']) !== 'query') {
            throw new InvalidArgumentException('Invalid action. Omit action parameter or use request() method');
        }

        $parameters = array_merge(['action' => 'query'], $parameters);

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
