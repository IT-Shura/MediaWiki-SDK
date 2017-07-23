<?php

namespace Tests\MediaWiki\HttpClient;

use MediaWiki\HttpClient\GuzzleHttpClient;

class GuzzleHttpClientTest
{
    public function testCreate()
    {
        new GuzzleHttpClient();
    }
}