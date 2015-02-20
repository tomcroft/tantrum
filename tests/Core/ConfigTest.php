<?php

namespace tantrum\tests;

use tantrum\Core;

class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function initSucceeds()
    {
        $this->assertEquals('tantrum\Core\Config', get_class(Core\Config::init()));
    }

    /**
     * @test
     */
    public function setSucceeds()
    {
        $configOptions = array(
            'host'     => uniqid(),
            'schema'   => uniqid(),
            'user'     => uniqid(),
            'password' => uniqid() 
        );
        $config = Core\Config::init();
        $config::setDatabase('mysql', $configOptions['host'], $configOptions['schema'], $configOptions['user'], $configOptions['password'], true);
        $this->assertEquals($configOptions, $config::getDatabase('mysql'));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException 
     */
    public function setThrowsException()
    {
        $config = Core\Config::init();
        $config::setDatabase(uniqid(), uniqid(), uniqid(), uniqid(), uniqid());
    }

    /**
     * @test
     * @expectedException tantrum\Exception\Exception
     */
    public function getThrowsException()
    {
        $config = Core\Config::init();
        $config::getDatabase(uniqid());
    }
}