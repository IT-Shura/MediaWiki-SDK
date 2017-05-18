<?php

namespace MediaWiki\HttpClient;

interface HttpClientInterface
{
    /**
     * Constructor.
     *
     * @param array $cookies
     * @param array $headers
     */
    public function __construct(array $cookies = [], array $headers = []);

    /**
     * Makes a HTTP request to the specified URL with the specified parameters.
     *
     * @param string $method
     * @param string $url
     * @param array  $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function request($method, $url, array $parameters = [], array $headers = [], array $cookies = []);

    /**
     * Makes a GET HTTP request to the specified URL.
     *
     * @param string $url
     * @param string $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function get($url, array $parameters = [], array $headers = [], array $cookies = []);

    /**
     * Makes a POST HTTP request to the specified URL.
     *
     * @param string $url
     * @param string $parameters
     * @param array  $headers
     * @param array  $cookies
     *
     * @return string
     */
    public function post($url, array $parameters = [], array $headers = [], array $cookies = []);

    /**
     * Returns received cookies.
     *
     * @return array
     */
    public function getCookies();
}
