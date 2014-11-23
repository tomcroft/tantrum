<?php

namespace tantrum\tests;

use tantrum\Core;

class CacheTest extends TestCase 
{
    /**
     * @test
     */
    public function initSucceeds()
    {
        $this->assertEquals('tantrum\Core\Cache', get_class(Core\Cache::init()));
    }

    /**
     * @test
     * @dataProvider setDataProvider
     */
    public function setSucceeds($value)
    {
        $key = uniqid();
        $cache = Core\Cache::init();
        $cache::set($key, $value);
        $this->assertSame($value, $cache::get($key));
    }


    // Data Providers
    
    public function setDataProvider()
    {
        return array(
            array(1234),
            array('String'),
            array(array('one' => 'two')),
            array(new \stdclass),
            array('true')
        );
    }

    // Utils

    public function setUp()
    {
        $cache = Core\Cache::init();
        $cache->flush(); 
    }

    public function tearDown()
    {
        $cache = Core\Cache::init();
        $cache->flush();
    }
}