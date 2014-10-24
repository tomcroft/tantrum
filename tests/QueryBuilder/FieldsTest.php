<?php

namespace tantrum\tests;

use tantrum\QueryBuilder;

class FieldsTest extends TestCase
{
    /**
     * @test
     */
    public function constructSucceeds()
    {
        $argument1 = array('thisThing' => 'thisThing');
        $argument2 = array('thisOtherThing' => 'value');
        $fields = new QueryBuilder\Fields(key($argument1), $argument2);
        $this->assertEquals(array_merge($argument1, $argument2), $fields->toArray());
    }
}