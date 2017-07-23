<?php

namespace Tests\MediaWiki\HttpClient;

use MediaWiki\HttpClient\CurlHttpClient;

class CurlHttpClientTest
{
    public function testCreate()
    {
        new CurlHttpClient();
    }
}