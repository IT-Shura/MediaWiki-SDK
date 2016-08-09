<?php

namespace Tests;

use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        if (class_exists('Mockery')) {
            \Mockery::close();
        }
    }
}
