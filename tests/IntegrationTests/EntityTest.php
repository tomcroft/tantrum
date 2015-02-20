<?php

namespace tantrum\tests\IntegrationTests;

use tantrum\Database;

class EntityTest extends TestCase
{
    protected $mod;

    /**
     * @test
     */
    public function loadByKey()
    {
        $manager    = Database\Manager::init();
        $connection = $manager->getConnection('mysql', 'main');
        $user    = $connection->getEntity('user');
        $user->loadByKey('userId', 'abc-123-def-456');
    }

    public function setUp()
    {
        parent::setUp();
    }    
}