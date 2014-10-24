<?php

namespace tantrum\tests;

use tantrum\QueryBuilder;

class ClauseCollectionTest extends TestCase
{

    /**
     * @test
     * @dataProvider getTypeValidDataProvider
     */
    public function setTypeSucceeds($type)
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->setType($type);
        $this->assertEquals($type, $clauseCollection->getType());
    }

    /**
     * @test
     * @expectedException \tantrum\Exception\ClauseException
     */
    public function setTypeThrowsClauseException()
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->setType(uniqid());
    }

    /**
     * @test
     */
    public function addClauseSucceeds()
    {
        $clause = new QueryBuilder\Clause();
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->addClause($clause);
        $this->assertSame($clause, $clauseCollection->toArray()[0]);
    }

    /**
     * @test
     */
    public function addClauseWithMultipleClausesSucceeds()
    {
        $clause0 = new QueryBuilder\Clause();
        $clause1 = new QueryBuilder\Clause();
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->addClause($clause0);
        $clauseCollection->addClause($clause1);
        $this->assertSame($clause0, $clauseCollection->toArray()[0]);
        $this->assertSame($clause1, $clauseCollection->toArray()[1]);
    }

    /**
     * @test
     * @dataProvider staticValidDataProvider
     */
    public function _andSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->_And($left, $right, $operator, $escaped);
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_AND, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @dataProvider staticValidDataProvider
     */
    public function _orSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->_Or($left, $right, $operator, $escaped);
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_OR, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @dataProvider staticValidDataProvider
     */
    public function callWithAndSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->And($left, $right, $operator, $escaped);
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_AND, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }

    /**
     * @test
     * @expectedException \tantrum\Exception\ClauseException
     * @expectedExceptionMessage Method "randomFunctionName" not handled
     */
    public function callThrowsClauseException()
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->randomFunctionName();
    }

    /**
     * @test
     * @dataProvider staticValidDataProvider
     */
    public function callWithOrSucceeds($left, $right, $operator, $escaped)
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $clauseCollection->Or($left, $right, $operator, $escaped);
        $clause = $clauseCollection->toArray()[0];
        $this->assertEquals('tantrum\QueryBuilder\Clause', get_class($clause));
        $this->assertEquals(QueryBuilder\Clause::_OR, $clause->getType());
        $this->assertEquals(array($left, $right), $clause->getArgs());
        $this->assertEquals($operator, $clause->getOperator());
        $this->assertEquals($escaped, $clause->isEscaped());
    }


    // Data Providers

    public function getTypeValidDataProvider()
    {
        return array(
            array(QueryBuilder\Clause::WHERE),
            array(QueryBuilder\Clause::_AND),
            array(QueryBuilder\Clause::_OR),
            array(QueryBuilder\Clause::ON),
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