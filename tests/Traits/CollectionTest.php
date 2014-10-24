<?php

namespace tantrum\tests;

class Collection extends TestCase
{
    /**
     * @test
     */
    public function __setSucceeds()
    {
        $key = uniqid();
        $value = uniqid();
        $mock = $this->getMockForTrait('tantrum\Traits\Collection');
        $mock->$key = $value;
        $this->assertEquals(array($key => $value), $mock->toArray());
    }

    /**
     * @test
     */
    public function __getSucceeds()
    {
        $key = uniqid();
        $value = uniqid();
        $mock = $this->getMockForTrait('tantrum\Traits\Collection');
        $mock->$key = $value;
        $this->assertEquals($value, $mock->$key);
    }

    /**
     * @test
     */
    public function countSucceeds()
    {
        $mock = $this->getMockForTrait('tantrum\Traits\Collection');
        $values = array(
            'value1',
            'value2',
            'value3'
        );
        foreach($values as $value) {
            $mock->$value = $value;
        }

        $this->assertEquals(count($values), $mock->count());
    }

    /**
     * @test
     */
    public function isEmptySucceeds()
    {
        $mock = $this->getMockForTrait('tantrum\Traits\Collection');
        $this->assertTrue($mock->isEmpty());
        $mock->thing = 'otherThing';
        $this->assertFalse($mock->isEmpty());
    }
}