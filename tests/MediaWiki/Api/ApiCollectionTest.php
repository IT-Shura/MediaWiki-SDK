<?php

namespace Tests\MediaWiki\Api;

use MediaWiki\Api\Api;
use MediaWiki\Api\ApiCollection;
use Mediawiki\HttpClient\HttpClientInterface;
use Mediawiki\Storage\StorageInterface;
use Mockery;
use Tests\TestCase;

class ApiCollectionTest extends TestCase
{
    public function testConstructor()
    {
        $apiCollection = new ApiCollection();

        $this->assertEquals([], $apiCollection->getAll());

        $api = [
            'en' => $this->createApi('en'),
            'ru' => $this->createApi('ru'),
        ];

        $apiCollection = new ApiCollection($api);

        $this->assertEquals($api, $apiCollection->getAll());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWithNonArray()
    {
        new ApiCollection(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorWithInvalidLanguageCodeType()
    {
        $api = [
            $this->createApi('en'),
        ];

        // throws InvalidArgumentException because language code must be a string
        new ApiCollection($api);
    }

    public function testConstructorWithInvalidApiType()
    {
        if (PHP_VERSION_ID >= 70000) {
            $this->setExpectedException('TypeError');
        } else {
            $this->setExpectedException('PHPUnit_Framework_Error');
        }

        $api = [
            'en' => null,
        ];

        new ApiCollection($api);
    }

    public function testAdd()
    {
        $api = $this->createApi('en');

        $apiCollection = new ApiCollection();

        $apiCollection->add('en', $api);

        $this->assertEquals(['en' => $api], $apiCollection->getAll());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddWithInvalidLanguageCodeType()
    {
        $api = $this->createApi('en');

        $apiCollection = new ApiCollection();

        $apiCollection->add(null, $api);
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
    public function testGetWithInvalidLanguageCodeType()
    {
        $apiCollection = new ApiCollection();

        $apiCollection->get(null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetNotExistingApi()
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testHasWithInvalidLanguageCodeType()
    {
        $apiCollection = new ApiCollection();

        $apiCollection->has(null);
    }

    public function testGetLanguages()
    {
        $apiCollection = new ApiCollection();

        $this->assertEquals([], $apiCollection->getLanguages());

        $api = [
            'en' => $this->createApi('en'),
            'ru' => $this->createApi('ru'),
        ];

        $apiCollection = new ApiCollection($api);

        $this->assertEquals(['en', 'ru'], $apiCollection->getLanguages());
    }

    protected function createApi($language)
    {
        $url = sprintf('http://%s.wikipedia.org/w/api.php', $language);

        $client = Mockery::mock(HttpClientInterface::class);
        $storage = Mockery::mock(StorageInterface::class);

        $storage->shouldReceive('get');

        return new Api($url, $client, $storage);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testEnableQueryLog()
    {
        $apiEn = Mockery::mock('overload:MediaWiki\Api\Api');
        $apiRu = Mockery::mock('overload:MediaWiki\Api\Api');

        $apiEn->shouldReceive('enableQueryLog')->once();
        $apiRu->shouldReceive('enableQueryLog')->once();

        $apiCollection = new ApiCollection([
            'en' => $apiEn,
            'en' => $apiRu,
        ]);

        $apiCollection->enableQueryLog();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDisableQueryLog()
    {
        $apiEn = Mockery::mock('overload:MediaWiki\Api\Api');
        $apiRu = Mockery::mock('overload:MediaWiki\Api\Api');

        $apiEn->shouldReceive('disableQueryLog')->once();
        $apiRu->shouldReceive('disableQueryLog')->once();

        $apiCollection = new ApiCollection([
            'en' => $apiEn,
            'ru' => $apiRu,
        ]);

        $apiCollection->disableQueryLog();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetQueryLog()
    {
        $apiEn = Mockery::mock('overload:MediaWiki\Api\Api');
        $apiRu = Mockery::mock('overload:MediaWiki\Api\Api');

        $apiEn->shouldReceive('getQueryLog')->once()->andReturn(['foo' => 'bar']);
        $apiRu->shouldReceive('getQueryLog')->once()->andReturn(['baz' => 'qux']);

        $apiCollection = new ApiCollection([
            'en' => $apiEn,
            'ru' => $apiRu,
        ]);

        $expectedQueryLog = [
            'en' => ['foo' => 'bar'],
            'ru' => ['baz' => 'qux'],
        ];

        $this->assertEquals($expectedQueryLog, $apiCollection->getQueryLog());

        $api = Mockery::mock('overload:MediaWiki\Api\Api');

        $api->shouldReceive('getQueryLog')->withArgs([null, null])->once();
        $api->shouldReceive('getQueryLog')->withArgs([['method', 'response'], null])->once();
        $api->shouldReceive('getQueryLog')->withArgs([null, 3])->once();
        $api->shouldReceive('getQueryLog')->withArgs([['method', 'response'], 3])->once();

        $apiCollection = new ApiCollection([
            'en' => $api,
        ]);

        $apiCollection->getQueryLog();
        $apiCollection->getQueryLog(['method', 'response']);
        $apiCollection->getQueryLog(null, 3);
        $apiCollection->getQueryLog(['method', 'response'], 3);
    }
}
