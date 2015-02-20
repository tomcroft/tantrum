<?php

namespace tantrum\tests;

use tantrum\Core;

class ContainerTest extends TestCase
{
   /**
    * @test
    */
   public function initSucceeds()
   {
        $this->assertEquals('tantrum\Core\Container', get_class(Core\Container::init()));
   }

   /**
    * @test
    */
   public function newInstanceSucceeds()
   {
        $class = 'stdClass';
        $container = Core\Container::init();
        $this->assertEquals($class, get_class($container::newInstance($class, array())));
   }

   /**
    * @test
    */
   public function newInstanceReturnsInjectedModule()
   {
        $class = new \stdClass();
        $className = uniqid();
        $container = Core\Container::init();
        $this->assertSame($class, $container::injectInstance($className, $class));
        $this->assertSame($class, $container::newInstance($className, array()));
   }

   /**
    * @test
    */
   public function injectInstanceReturnsPrevisouslyInjectedModule()
   {
        $class = new \stdClass();
        $className = uniqid();
        $container = Core\Container::init();
        $this->assertSame($class, $container::injectInstance($className, $class));
        $this->assertSame($class, $container::injectInstance($className, $class));
   }
}