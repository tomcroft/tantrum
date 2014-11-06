<?php

namespace tantrum\tests;

use tantrum\Entity,
    tantrum\Core,
    tantrum\QueryBuilder;

class EntityTest extends TestCase
{
    protected $mod;
    protected $columns = array();
    protected $schema;
    protected $database;
    protected $table; 

    /**
     * @test
     */
    public function getSucceeds()
    {
        $handle = uniqid();

        $entity = $this->mock('tantrum\Entity\Entity');
        $entity->shouldReceive('setHandle')->times(1)
            ->with($handle)
            ->andReturn(true);

        $this->assertSame($entity, Entity\Entity::get($handle));
    }

    /**
     * @test
     */
    public function setHandleSucceeds()
    {
        $handle = 'db.table';
        $this->mod->setHandle($handle);
        $this->assertEquals($handle, $this->mod->getHandle());
    }

    /**
     * @test
     * @dataProvider invalidHandles
     * @expectedException tantrum\Exception\EntityException
     */
    public function setHandleThrowsEntityException($handle)
    {
        $this->mod->setHandle($handle);
    }

    /**
     * @test
     */
    public function __setSucceeds()
    {
        $this->injectMocks(true);
        $this->mod->firstName = 'newFirstName';
        $this->assertEquals($this->mod->firstName, 'newFirstName');
    }

    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __setThrowsEntityException()
    {
        $this->injectMocks(true);
        $this->mod->oldFirstName = 'newFirstName';
    }

    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __getThrowsEntityException()
    {
        $this->injectMocks(true);
        $var = $this->mod->oldFirstName;
    }

    /**
     * @test
     */
    public function isModifiedReturnsFalse()
    {
        $this->injectMocks(true);
        $this->assertFalse($this->mod->isModified());
    }

    /**
     * @test
     */
    public function isModifiedReturnsTrue()
    {
        $this->injectMocks(true);
        $this->mod->firstName = 'newFirstName';
        $this->assertTrue($this->mod->isModified());
    }

    /**
     * @test
     */
    public function saveReturnsFalse()
    {
        $this->injectMocks(true);
        $this->assertFalse($this->mod->save());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function saveCallsCreate()
    {
        $userId = uniqid();
        $this->injectMocks(false);
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $query->shouldReceive('Insert')->once()
            ->andReturn(true);
        $db = $this->mock('tantrum\mysql\mysql');
        $db->shouldReceive('query')->once()
            ->andReturn(true);
        $db->shouldReceive('getInsertId')->once()
            ->andReturn($userId);
        $this->mod->firstName = 'newFirstName';
        $this->assertTrue($this->mod->save());
        $this->assertFalse($this->mod->isModified());
        $this->assertEquals($userId, $this->mod->userId);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function saveCallsUpdate()
    {
        $this->injectMocks(true);
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $query->shouldReceive('Update')->once()
            ->andReturn($query);
        $query->shouldReceive('Where')->once()
            ->with('userId', 'userId')
            ->andReturn($query);
        $db = $this->mock('tantrum\mysql\mysql');
        $db->shouldReceive('query')->once()
            ->andReturn(true);
        $this->mod->firstName = 'newFirstName';
        $this->assertTrue($this->mod->isModified());
        $this->assertTrue($this->mod->save());
        $this->assertFalse($this->mod->isModified());
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function loadByKeySucceeds()
    {
        $this->injectMocks(true);
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $query->shouldReceive('Select')->once()
            ->andReturn($query);
        $query->shouldReceive('Where')->once()
            ->with('userId', 'userId')
            ->andReturn($query);
        $db = $this->mock('tantrum\mysql\mysql');
        $db->shouldReceive('query')->once()
            ->andReturn(true);
        $db->shouldReceive('fetch')->once()
            ->andReturn(array(
                'userId'    => 'userId',
                'userName'  => 'userName',
                'firstName' => 'firstName',
                'lastName'  => 'lastName,',
                'addressId' => 'addressId',
            ));
        $this->assertTrue($this->mod->loadByKey('userId', 'userId'));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __callTWithWrongPropertyhrowsEntityException()
    {
        $this->injectMocks();
        $var = $this->mod->userName();
    }

    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __callWithNonExistantPropertyThrowsEntityException()
    {
        $this->injectMocks();
        $var = $this->mod->thisDoesNotExist();
    }

    /**
     * @test - not possible at present
     * @xdepends getSucceeds
     */
    public function __callSucceeds()
    {
        $address = $this->mock('tantrum\Entity\Entity');
        $address->shouldReceive('setHandle')->once()
            ->with('main.address')
            ->andReturn(true);
        $address->shouldReceive('loadByKey')->once()
            ->with('addressId', 'addressId')
            ->andReturn(true);
        $this->injectMocks(true);
        $thing = $this->mod->addressId();
    }

    // Data Providers
    
    public function invalidHandles()
    {
        return array(
            array('string'),
            array(null),
            array(''),
            array(' '),
            array(array()),
        );
    }


    // Utils

    public function setUp()
    {
        parent::setUp();
        $this->mod = new Entity\Entity();
    }

    protected function injectMocks($setPrimaryKey = false)
    {
        $this->database = 'main'; 
        $this->table = 'user';
        $this->schema = sprintf('%s.%s', $this->table, $this->database);

        $this->mod->setHandle($this->schema);

        $this->columns = array(
            $this->createFieldObject('userId', 'PRI', $setPrimaryKey ? 'userId' : null), 
            $this->createFieldObject('userName', null, 'userName'),
            $this->createFieldObject('firstName', null, 'firstName'),
            $this->createFieldObject('lastName', null, 'lastName'),
            $this->createFieldObject('addressId', null, 'addressId', 'main', 'address', 'addressId'),
        );

        $db = $this->mock('tantrum\mysql\mysql');
        $db->shouldReceive('getColumnDefinitions')->once()
            ->with($this->table)
            ->andReturn($this->columns);
 
        $manager = $this->mockStatic('tantrum\Database\Manager');
        $manager->shouldReceive('get')->once()
            ->with($this->database)
            ->andReturn($db);
    }

    protected function createFieldObject($columnName, $columnKey, $value, $joinDatabase = null, $joinTable = null, $joinOn = null)
    {
        $field = new QueryBuilder\Field();
        $reflectionClass = new \ReflectionClass($field);

        // Set Column Name
        $reflectionPropertyColumnName = $reflectionClass->getProperty('columnName');
        $reflectionPropertyColumnName->setAccessible(true);
        $reflectionPropertyColumnName->setValue($field, $columnName);

        // Set Column Key
        $reflectionPropertyColumnKey = $reflectionClass->getProperty('columnKey');
        $reflectionPropertyColumnKey->setAccessible(true);
        $reflectionPropertyColumnKey->setValue($field, $columnKey);

        // Set joinDatabase
        $reflectionPropertyJoinDatabase = $reflectionClass->getProperty('joinDatabase');
        $reflectionPropertyJoinDatabase->setAccessible(true);
        $reflectionPropertyJoinDatabase->setValue($field, $joinDatabase);

        // Set joinTable
        $reflectionPropertyJoinTable = $reflectionClass->getProperty('joinTable');
        $reflectionPropertyJoinTable->setAccessible(true);
        $reflectionPropertyJoinTable->setValue($field, $joinTable);

        // Set joinSchema
        $reflectionPropertyJoinOn = $reflectionClass->getProperty('joinOn');
        $reflectionPropertyJoinOn->setAccessible(true);
        $reflectionPropertyJoinOn->setValue($field, $joinOn);

        // Set Value
        $reflectionPropertyValue = $reflectionClass->getProperty('value');
        $reflectionPropertyValue->setAccessible(true);
        $reflectionPropertyValue->setValue($field, $value);
        
        return $field;
    }
    
}