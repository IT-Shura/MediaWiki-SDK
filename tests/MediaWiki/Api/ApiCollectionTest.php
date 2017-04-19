<?php

namespace Tests\MediaWiki;

use Tests\TestCase;
use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use Mockery;

class ApiCollectionTest extends TestCase
{
    public function testConstructor()
    {
        // without arguments
        $apiCollection = new ApiCollection();

        $api = [
            'en' => $this->createApi('en'),
            'ru' => $this->createApi('ru'),
        ];

        $apiCollection = new ApiCollection($api);

        $this->assertEquals($api, $apiCollection->getAll());
    }

    public function testConstructorWithInvalidData()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->setExpectedException('TypeError');
        } else {
            $this->setExpectedException('PHPUnit_Framework_Error');
        }


        $api = [
            'en' => null,
        ];

        $apiCollection = new ApiCollection($api);
    }

    public function testAdd()
    {
        $api = $this->createApi('en');

        $apiCollection = new ApiCollection();

        $apiCollection->add('en', $api);

        $this->assertEquals(['en' => $api], $apiCollection->getAll());
    }

    public function testGet()
    {
        $api = $this->createApi('en');

        $apiCollection = new ApiCollection(['en' => $api]);

        $this->assertEquals($api, $apiCollection->get('en'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetNotExistenApi()
    {
        $apiCollection = new ApiCollection();

        $apiCollection->get('foo');
    }

    public function testHas()
    {
        $api = $this->createApi('en');

        $apiCollection = new ApiCollection();

        $this->assertFalse($apiCollection->has('en'));

        $apiCollection->add('en', $api);

        $this->assertTrue($apiCollection->has('en'));
    }

    protected function createApi($language)
    {
        $url = sprintf('http://%s.wikipedia.org/w/api.php', $language);

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $storage->shouldReceive('get');

        return new Api($url, $client, $storage);
    }
}
