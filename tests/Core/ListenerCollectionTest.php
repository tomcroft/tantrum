<?php

namespace tantrum\tests;

use tantrum\Core;

class ListenerCollectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider defaultListenerDataProvider
     */
    public function initCreatesDefaultListsners($call, $params, $return)
    {
        $listeners = Core\ListenerCollection::init();
        $this->assertEquals($return, $listeners->callListener($call, $params));
    }

    /**
     * @test
     */
    public function addListenerSucceeds()
    {
        $value1 = 10;
        $value2 = 20;
        $expected = 200;
        $listener = function($var1, $var2){
            return $var1 * $var2;
        };
        $listeners = Core\ListenerCollection::init();
        $listeners->addListener('listener', $listener);
        $this->assertEquals($expected, $listeners->callListener('listener', array($value1, $value2)));
    }

    /**
     * @test
     * @dataProvider invalidListenersDataProvider
     * @expectedException tantrum\Exception\Exception
     */
    public function addListenerThrowsException($invalidCallable)
    {
        $listeners = Core\ListenerCollection::init();
        $listeners->addListener(uniqid(), $invalidCallable);
    }

    /**
     * @test
     */
    public function callListenerreturnsFalse()
    {
        $listeners = Core\ListenerCollection::init();
        $this->assertFalse($listeners->callListener('notAListener', array('var')));
    }

    // Data Providers
    
    public function defaultListenerDataProvider()
    {
        return array(
            array('mapColumnName', array('columnName'), 'columnName'),
        );
    }

    public function invalidListenersDataProvider()
    {
        return array(
            array(123),
            array('string'),
            array(array()),
            array(false),
            array(new \stdclass()),
        );
    }
}