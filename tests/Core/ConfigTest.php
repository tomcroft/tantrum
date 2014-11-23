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
            'databaseDriver'   => uniqid(),
            'databaseHost'     => uniqid(),
            'defaultSchema'    => uniqid(),
            'databaseUser'     => uniqid(),
            'databasePassword' => uniqid(),
        );

        $config = Core\Config::init();
        $config::set($configOptions);

        foreach($configOptions as $key => $value) {
            $this->assertSame($value, $config::get($key));
        }
    }

    /**
     * @test
     * @expectedException tantrum\Exception\Exception 
     */
    public function setThrowsException()
    {
        $configOptions = array(
            uniqid() => uniqid(),
        );
        $config = Core\Config::init();
        $config::set($configOptions);
    }

    /**
     * @test
     * @expectedException tantrum\Exception\Exception
     */
    public function getThrowsException()
    {
        $configOptions = array(
            'databaseDriver'   => uniqid(),
            'databaseHost'     => uniqid(),
            'defaultSchema'    => uniqid(),
            'databaseUser'     => uniqid(),
            'databasePassword' => uniqid(),
        );

        $config = Core\Config::init();
        $config::set($configOptions);

        $config::get(uniqid());
    }
}