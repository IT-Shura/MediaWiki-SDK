<?php

namespace MediaWiki\Api;

use LogicException;
use InvalidArgumentException;
use RuntimeException;

class QueryLog
{
    /**
     * @var array
     */
    protected $queryLog = [];

    /**
     * @var array
     */
    protected $availableFields = ['method', 'parameters', 'headers', 'cookies', 'response'];

    /**
     * @param string $method
     * @param array $parameters
     * @param array $headers
     * @param array $cookies
     */
    public function logQuery($method, $parameters, $headers, $cookies)
    {
        $this->queryLog[] = [
            'method' => $method,
            'parameters' => $parameters,
            'headers' => $headers,
            'cookies' => $cookies,
        ];
    }

    /**
     * @param $response
     */
    public function appendResponse($response)
    {
        $lastLogRecord = array_pop($this->queryLog);

        $lastLogRecord['response'] = $response;

        $this->queryLog[] = $lastLogRecord;
    }

    /**
     * @param string[]|null $fields
     * @param int|null $count
     *
     * @return array
     */
    public function getLog($fields = null, $count = null)
    {
        $defaultFields = ['method', 'parameters', 'response'];

        $fields = $fields === null ? $defaultFields : $fields;

        if (count(array_diff($fields, $this->availableFields)) > 0) {
            $unknownFields = array_diff($fields, $this->availableFields);

            throw new RuntimeException(sprintf('Unknown log fields: %s', implode(', ', $unknownFields)));
        }

        if (count($fields) === 0) {
            throw new LogicException('At least one field should be specified');
        }

        if (!is_int($count) and $count !== null) {
            throw new InvalidArgumentException(sprintf('%s expects parameter 2 to be integer or null, %s given', __METHOD__, gettype($count)));
        }

        $log = $count === null ? $this->queryLog : array_slice($this->queryLog, $count * -1);

        if ($fields === $this->availableFields) {
            return $log;
        }

        $result = [];

        foreach ($log as $record) {
            $newRecord = [];

            foreach ($fields as $field) {
                if (!array_key_exists($field, $record)) {
                    continue;
                }

                $newRecord[$field] = $record[$field];
            }

            $result[] = $newRecord;
        }

        return $result;
    }

    /**
     * Clears query log and returns it.
     *
     * @return array
     */
    public function clearLog()
    {
        $temp = $this->queryLog;

        $this->queryLog = [];

        return $temp;
    }
}
