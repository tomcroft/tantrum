<?php

namespace tantrum\tests;

use tantrum\QueryBuilder;

class FieldTest extends TestCase
{
    /**
     * @test
     */
    public function setValueSucceeds()
    {
        $value = uniqid();
        $field = new QueryBuilder\Field();
        $this->assertFalse($field->isModified());
        $field->setValue($value);
        $this->assertEquals($value, $field->getValue());
        $this->assertTrue($field->isModified());
    }

    /**
     * @test
     */
    public function isPrimaryReturnsFalse()
    {
        $field = new QueryBuilder\Field();
        $this->assertFalse($field->isPrimary());
    }

    /**
     * @test
     */
    public function isPrimaryReturnsTrue()
    {
        $field = new QueryBuilder\Field();
        $reflectionClass = new \ReflectionClass($field);
        $reflectionProperty = $reflectionClass->getProperty('columnKey');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($field, 'PRI'); 
        $this->assertTrue($field->isPrimary());
    }

    /**
     * @test
     */
    public function getJoinSchemaReturnsNull()
    {
        $field = new QueryBuilder\Field();
        $this->assertNull($field->getJoinSchema());
    }

    /**
     * @test
     */
    public function getJoinSchemaSucceeds()
    {
        $database = uniqid();
        $table = uniqid();
        $field = new QueryBuilder\Field();
        $reflectionClass = new \ReflectionClass($field);
        $reflectionPropertyDatabase = $reflectionClass->getProperty('joinDatabase');
        $reflectionPropertyDatabase->setAccessible(true);
        $reflectionPropertyDatabase->setValue($field, $database);
        $reflectionPropertyTable = $reflectionClass->getProperty('joinTable');
        $reflectionPropertyTable->setAccessible(true);
        $reflectionPropertyTable->setValue($field, $table); 
        $this->assertEquals($database.'.'.$table, $field->getJoinSchema());
    }

    /**
     * @test
     */
    public function getJoinOnReturnsNull()
    {
        $field = new QueryBuilder\Field();
        $this->assertNull($field->getJoinOn());
    }

    /**
     * @test
     */
    public function getJoinOnSucceeds()
    {
        $joinOn = uniqid();
        $field = new QueryBuilder\Field();
        $reflectionClass = new \ReflectionClass($field);
        $reflectionProperty = $reflectionClass->getProperty('joinOn');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($field, $joinOn);
        $this->assertEquals($joinOn, $field->getJoinOn());
    }

    /**
     * @test
     */
    public function getColumnNameReturnsNull()
    {
        $field = new QueryBuilder\Field();
        $this->assertNull($field->getColumnName());
    }

    /**
     * @test
     */
    public function getColumnNameSucceeds()
    {
        $columnName = uniqid();
        $field = new QueryBuilder\Field();
        $reflectionClass = new \ReflectionClass($field);
        $reflectionProperty = $reflectionClass->getProperty('columnName');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($field, $columnName);
        $this->assertEquals($columnName, $field->getColumnName());
    }

    /**
     * @test
     */
    public function setModifiedSucceeds()
    {
        $field = new QueryBuilder\Field();
        $this->assertFalse($field->isModified());
        $field->setModified(true);
        $this->assertTrue($field->isModified());
        $field->setModified(false);
        $this->assertFalse($field->isModified());
    }
}