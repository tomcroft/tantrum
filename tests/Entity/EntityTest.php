<?php

namespace tantrum\tests;

use tantrum\Database,
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
    public function __setSucceeds()
    {
        $this->markTestIncomplete();
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
        $this->markTestIncomplete();
        $this->injectMocks(true);
        $this->mod->oldFirstName = 'newFirstName';
    }


    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __getThrowsEntityException()
    {
        $this->markTestIncomplete();
        $this->injectMocks(true);
        $var = $this->mod->oldFirstName;
    }

    /**
     * @test
     */
    public function isModifiedReturnsFalse()
    {
        $this->markTestIncomplete();
        $this->injectMocks(true);
        $this->assertFalse($this->mod->isModified());
    }

    /**
     * @test
     */
    public function isModifiedReturnsTrue()
    {
        $this->markTestIncomplete();
        $this->injectMocks(true);
        $this->mod->firstName = 'newFirstName';
        $this->assertTrue($this->mod->isModified());
    }

    /**
     * @test
     */
    public function saveReturnsFalse()
    {
        $this->markTestIncomplete();
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
        $this->markTestIncomplete();
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $userId = uniqid();
        $this->injectMocks(false);
        $query->shouldReceive('Insert')->once()
            ->andReturn(true);
        $db = $this->mock('tantrum_mysql_adaptor');
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
        $this->markTestIncomplete();
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $this->injectMocks(true);
        $query->shouldReceive('Update')->once()
            ->andReturn($query);
        $query->shouldReceive('Where')->once()
            ->with('userId', 'userId')
            ->andReturn($query);
        $db = $this->mock('tantrum_mysql_adaptor');
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
        $this->markTestIncomplete();
        $query = $this->mockStatic('tantrum\QueryBuilder\Query');
        $this->injectMocks(true);
        $query->shouldReceive('Select')->once()
            ->andReturn($query);
        $query->shouldReceive('Where')->once()
            ->with('userId', 'userId')
            ->andReturn($query);
        $db = $this->mock('tantrum_mysql_adaptor');
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
    public function __callWithWrongPropertyThrowsEntityException()
    {
        $this->markTestIncomplete();
        $this->injectMocks();
        $var = $this->mod->userName();
    }

    /**
     * @test
     * @expectedException tantrum\Exception\EntityException
     */
    public function __callWithNonExistantPropertyThrowsEntityException()
    {
        $this->markTestIncomplete();
        $this->injectMocks();
        $var = $this->mod->thisDoesNotExist();
    }

    /**
     * @test - not possible at present
     * @xdepends getSucceeds
     */
    public function __callSucceeds()
    {
        $this->markTestIncomplete();
        $address = $this->mock('tantrum\Database\Entity');
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
        $this->mod = new Database\Entity();
    }

    protected function injectMocks($setPrimaryKey = false)
    {
        $this->database = 'main'; 
        $this->table = 'user';
        $this->schema = sprintf('%s.%s', $this->database, $this->table);

        $this->mod->setHandle($this->schema);

        $this->columns = array(
            $this->createFieldObject('userId', 'PRI', $setPrimaryKey ? 'userId' : null), 
            $this->createFieldObject('userName', null, 'userName'),
            $this->createFieldObject('firstName', null, 'firstName'),
            $this->createFieldObject('lastName', null, 'lastName'),
            $this->createFieldObject('addressId', null, 'addressId', 'main', 'address', 'addressId'),
        );

        $db = $this->mock('tantrum_mysql_adaptor');
        $db->shouldReceive('getColumnDefinitions')->once()
            ->with($this->database, $this->table)
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