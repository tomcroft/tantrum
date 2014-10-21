<?php

namespace tests\lib;

use tomcroft\tantrum\QueryBuilder;

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
}