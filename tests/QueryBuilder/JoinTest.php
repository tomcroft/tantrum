<?php

namespace tantrum\tests;

use tantrum\QueryBuilder;

class JoinTest extends TestCase
{
    
    /**
     * @test
     */
    public function setAliasSucceeds()
    {
        $alias = uniqid();
        $join = new QueryBuilder\Join();
        $join->setAlias($alias);
        $this->assertEquals($alias, $join->getAlias());
    }

    /**
     * @test
     * @dataProvider validTypeDataProvider
     */
    public function setTypeSucceeds($type)
    {
        $join = new QueryBuilder\Join();
        $join->setType($type);
        $this->assertEquals($type, $join->getType());
    }

    /**
     * @test
     * @expectedException \tantrum\Exception\JoinException
     */
    public function setTypeThrowsJoinException()
    {
        $join = new QueryBuilder\Join();
        $join->setType(uniqid());
    }

    /**
     * @test
     */
    public function setTargetSucceeds()
    {
        $target = uniqid();
        $join = new QueryBuilder\Join();
        $join->setTarget($target);
        $this->assertEquals($target, $join->getTarget());
    }

    /**
     * @test
     */
    public function setClauseCollectionSucceeds()
    {
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $join = new QueryBuilder\Join();
        $join->setClauseCollection($clauseCollection);
        $this->assertSame($clauseCollection, $join->getClauseCollection());
    }

    /**
     * @test
     */
    public function innerSucceeds()
    {
        $target = uniqid();
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $join = QueryBuilder\Join::Inner($target, $clauseCollection);
        $this->assertEquals('tantrum\QueryBuilder\Join', get_class($join));
        $this->assertEquals($target, $join->getTarget());
        $this->assertEquals(QueryBuilder\Join::INNER, $join->getType());
        $this->assertSame($clauseCollection, $join->getClauseCollection());
    }

    /**
     * @test
     */
    public function leftSucceeds()
    {
        $target = uniqid();
        $clauseCollection = new QueryBuilder\ClauseCollection();
        $join = QueryBuilder\Join::Left($target, $clauseCollection);
        $this->assertEquals('tantrum\QueryBuilder\Join', get_class($join));
        $this->assertEquals($target, $join->getTarget());
        $this->assertEquals(QueryBuilder\Join::LEFT, $join->getType());
        $this->assertSame($clauseCollection, $join->getClauseCollection());
    }


    // Data Providers
    
    public function validTypeDataProvider()
    {
        return array(
            array(QueryBuilder\Join::INNER),
            array(QueryBuilder\Join::LEFT),
        );
    }
}