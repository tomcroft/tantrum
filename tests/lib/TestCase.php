<?php

namespace tests\lib;

use \Mockery;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public function mock($class)
    {
        $mock = \Mockery::mock($class)->makePartial();
        $key = get_parent_class($mock);
        return \tomcroft\tantrum\Core\Container::injectInstance($key, $mock);
    }

    public function assertArray($value)
    {
        $this->assertTrue(is_array($value));
    }

    public function tearDown()
    {
        \Mockery::close();
        \tomcroft\tantrum\Core\Container::flush();
    }
}