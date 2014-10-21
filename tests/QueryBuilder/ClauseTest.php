<?php

namespace tests\lib;

use tomcroft\tantrum\QueryBuilder;

class ClauseTest extends TestCase
{
    /**
     * @test
     * @dataProvider setArgsValidDataProvider
     */
    public function setArgsSucceeds($leftOperand, $rightOperand, $operator)
    {
        $clause = new QueryBuilder\Clause();
        $newClause = $clause->setArgs($leftOperand, $rightOperand, $operator);
        $this->assertEquals($clause, $newClause);
        $this->assertEquals(array($leftOperand, $rightOperand), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
    }

    /**
     * @test
     * @expectedException tomcroft\tantrum\Exception\ClauseException
     */
    public function setArgsThrowsClauseException()
    {
        $clause = new QueryBuilder\Clause(QueryBuilder\Clause::WHERE, true);
        $newClause = $clause->setArgs('leftOperand', 'rightOperand', uniqid());
    }

    /**
     * @test
     * @dataProvider isEscapedDataProvider
     */
    public function isEscapedSucceeds($escaped)
    {
        $clause = new QueryBuilder\Clause();
        $clause->setEscaped($escaped);
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @expectedException tomcroft\tantrum\Exception\ClauseException
     */
    public function setEscapedThrowsClausException()
    {
        $clause = new QueryBuilder\Clause();
        $clause->setEscaped(uniqid());
    }

    /**
     * @test
     * @dataProvider getTypeValidDataProvider
     */
    public function getTypeSucceeds($type)
    {
        $clause = new QueryBuilder\Clause();
        $clause->setType($type);
        $this->assertEquals($type, $clause->getType());
    }

    /**
     * @test
     * @covers tomcroft\tantrum\QueryBuilder\Clause::Where
     * @dataProvider staticValidDataProvider
     */
    public function whereSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = QueryBuilder\Clause::Where($left, $right, $operator, $escaped);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\ClauseCollection', get_class($clauseCollection));
        $this->assertEquals(QueryBuilder\Clause::WHERE, $clauseCollection->getType());
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::WHERE, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @covers tomcroft\tantrum\QueryBuilder\Clause::On
     * @dataProvider staticValidDataProvider
     */
    public function onSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = QueryBuilder\Clause::On($left, $right, $operator, $escaped);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\ClauseCollection', get_class($clauseCollection));
        $this->assertEquals(QueryBuilder\Clause::ON, $clauseCollection->getType());
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::ON, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }
    
    /**
     * @test
     * @covers tomcroft\tantrum\QueryBuilder\Clause::_And
     * @dataProvider staticValidDataProvider
     */
    public function _andSucceeds($left, $right, $operator, $escaped)
    {
        $clause = QueryBuilder\Clause::_And($left, $right, $operator, $escaped);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_AND, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @covers tomcroft\tantrum\QueryBuilder\Clause::_Or
     * @dataProvider staticValidDataProvider
     */
    public function _orSucceeds($left, $right, $operator, $escaped)
    {
        $clause = QueryBuilder\Clause::_Or($left, $right, $operator, $escaped);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_OR, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @dataProvider getTypeValidDataProvider
     */
    public function validateTypeSucceeds($type)
    {
        $this->assertTrue(QueryBuilder\Clause::validateType($type));
    }

    /**
     * @test
     * @expectedException tomcroft\tantrum\Exception\ClauseException
     */
    public function validateTypeThrowsClauseException()
    {
        QueryBuilder\Clause::validateType(uniqid());
    }


    // Data Providers

    public function isEscapedDataProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }

    public function getTypeValidDataProvider()
    {
        return array(
            array(QueryBuilder\Clause::WHERE),
            array(QueryBuilder\Clause::_AND),
            array(QueryBuilder\Clause::_OR),
            array(QueryBuilder\Clause::ON),
        );
    }

    public function setArgsValidDataProvider()
    {
        return array(
            array('leftArg', 'rightArg', QueryBuilder\Clause::EQUALS),
            array('leftArg', 'rightArg', QueryBuilder\Clause::NOT_EQUAL),
            array('leftArg', 'rightArg', QueryBuilder\Clause::GREATER_THAN),
            array('leftArg', 'rightArg', QueryBuilder\Clause::LESS_THAN),
        );

    }

    public function staticValidDataProvider()
    {
        return array(
            array('left', 'right', QueryBuilder\Clause::EQUALS, true),
            array('left', 'right', QueryBuilder\Clause::EQUALS, false),
            array('left', 'right', QueryBuilder\Clause::NOT_EQUAL, true),
            array('left', 'right', QueryBuilder\Clause::NOT_EQUAL, false),
            array('left', 'right', QueryBuilder\Clause::GREATER_THAN, true),
            array('left', 'right', QueryBuilder\Clause::GREATER_THAN, false),
            array('left', 'right', QueryBuilder\Clause::LESS_THAN, true),
            array('left', 'right', QueryBuilder\Clause::LESS_THAN, false),
        );
    }
}