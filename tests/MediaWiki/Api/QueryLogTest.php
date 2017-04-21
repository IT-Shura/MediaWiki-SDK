<?php

namespace Tests\MediaWiki\Api;

use Tests\TestCase;
use MediaWiki\Api\QueryLog;
use Mockery;

class QueryLogTest extends TestCase
{
    public function test()
    {
        $queryLog = new QueryLog();

        $queryLog->logQuery('GET', ['foo' => 'bar'], [], []);

        $queryLog->appendResponse('FooBarBaz');

        $queryLog->logQuery('POST', ['foo' => 'bar'], ['baz' => 'qux'], []);

        $expectedLog = [
            [
                'method' => 'GET',
                'parameters' => ['foo' => 'bar'],
                'response' => 'FooBarBaz',
            ],
            [
                'method' => 'POST',
                'parameters' => ['foo' => 'bar'],
            ],
        ];

        $this->assertEquals($expectedLog, $queryLog->getLog());

        $expectedLog = [
            [
                'method' => 'GET',
                'parameters' => ['foo' => 'bar'],
                'headers' => [],
                'cookies' => [],
                'response' => 'FooBarBaz',
            ],
            [
                'method' => 'POST',
                'parameters' => ['foo' => 'bar'],
                'headers' => ['baz' => 'qux'],
                'cookies' => [],
            ],
        ];

        $this->assertEquals($expectedLog, $queryLog->getLog(['method', 'parameters', 'headers', 'cookies', 'response']));

        $expectedLog = [
            [
                'method' => 'POST',
                'parameters' => ['foo' => 'bar'],
            ],
        ];

        $this->assertEquals($expectedLog, $queryLog->getLog(null, 1));

        $expectedLog = [
            [
                'method' => 'POST',
                'parameters' => ['foo' => 'bar'],
            ],
        ];

        $this->assertEquals($expectedLog, $queryLog->getLog(['method', 'parameters'], 1));

        $queryLog->clearLog();

        $this->assertEquals([], $queryLog->getLog());
    }
}
