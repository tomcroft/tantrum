<?php

namespace tantrum\tests;

use \Mockery;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public function mock($class)
    {
        $mock = \Mockery::mock($class)->makePartial();
        $key = get_parent_class($mock);
        return \tantrum\Core\Container::injectInstance($key, $mock);
    }

    public function assertArray($value)
    {
        $this->assertTrue(is_array($value));
    }

    public function tearDown()
    {
        \Mockery::close();
        \tantrum\Core\Container::flush();
    }
}