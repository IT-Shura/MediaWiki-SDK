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
    public function __construct($cookies = [], $headers = []);

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
    public function request($method, $url, $parameters = [], $headers = [], $cookies = []);

    /**
     * Makes a POST HTTP request to the specified URL.
     * 
     * @param  string $url
     * @param  string $parameters
     * 
     * @return string
     */
    public function post($url, $parameters);

    /**
     * Makes a GET HTTP request to the specified URL.
     * 
     * @param  string $url
     * @param  string $parameters
     * 
     * @return string
     */
    public function get($url, $parameters);

    /**
     * Returns received cookies.
     * 
     * @return array
     */
    public function getCookies();
}
