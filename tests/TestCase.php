<?php

namespace tantrum\tests;

use \Mockery,
    \tantrum\Core;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $container;

    public function mock($class, $methods = array())
    {
        $methodstring = '';
        if(count($methods) > 0) {
            $methodString = sprintf('[%s]', implode(',', $methods));
        }
        $mock = \Mockery::mock($class.$methodstring);
        return Core\Container::injectInstance($class, $mock);
    }

    public function mockStatic($class)
    {
        $mock = \Mockery::mock('alias:'.$class);
        return Core\Container::injectInstance($class, $mock);
    }

    public function assertArray($value)
    {
        $this->assertTrue(is_array($value));
    }

    public function setUp()
    {
        $configOptions = array(
            'databaseDriver'   => 'mysql',
            'databaseHost'     => 'localhost',
            'defaultSchema'    => 'information_schema',
            'databaseUser'     => 'databaseUser',
            'databasePassword' => 'databasePassword',
        );
        $config = \tantrum\Core\Config::init();
        $config->set($configOptions);
        $this->container = Core\Container::init();
    }

    public function tearDown()
    {
        \Mockery::close();
        Core\Container::flush();
        Core\Cache::flush();
    }
}