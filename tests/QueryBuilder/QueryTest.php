<?php

namespace tests\lib;

use tomcroft\tantrum\QueryBuilder;

class QueryTest extends TestCase
{
    /**
     * @test
     */
    public function setFieldsSucceeds()
    {
        $query = new QueryBuilder\Query();
        $fields = new QueryBuilder\Fields();
        $query->setFields($fields);
        $this->assertSame($fields, $query->getFields());
    }

    /**
     * @test
     */
    public function onDuplicateSucceeds()
    {
        $query = new QueryBuilder\Query();
        $fields = new QueryBuilder\Fields();
        $return = $query->OnDuplicate($fields);
        $this->assertSame($fields, $query->getDuplicateFieldsForUpdate());
        $this->assertSame($query, $return);
    }

    /**
     * @test
     */
    public function setTargetSucceeds()
    {
        $query = new QueryBuilder\Query();
        $target = 'database.table';
        $query->setTarget($target);
        $this->assertEquals($target, $query->getTarget());
    }

    /**
     * @test
     * @dataProvider validTypesDataProvider
     */
    public function setTypeSucceeds($type)
    {
        $query = new QueryBuilder\Query();
        $query->setType($type);
        $this->assertEquals($type, $query->getType());
    }

    /**
     * @test
     * @expectedException tomcroft\tantrum\Exception\QueryException
     */
    public function setTypeThrowsQueryException()
    {
        $query = new QueryBuilder\Query();
        $query->setType(uniqid());
    }

    /**
     * @test
     */
    public function setAliasSucceeds()
    {
        $query = new QueryBuilder\Query();
        $alias = uniqid();
        $query->setAlias($alias);
        $this->assertEquals($alias, $query->getAlias());
    }

    /**
     * @test
     */
    public function limitSucceeds()
    {
        $limit = 100;
        $offset = 25;
        $query = new QueryBuilder\Query();
        $query->Limit($offset, $limit);
        $this->assertEquals($limit, $query->getLimit());
        $this->assertEquals($offset, $query->getOffset());
    }

    /**
     * @test
     * @dataProvider invalidLimitDataProvider
     * @expectedException tomcroft\tantrum\Exception\QueryException
     */
    public function limitThrowsQueryException($offset, $limit)
    {
        $query = new QueryBuilder\Query();
        $query->Limit($offset, $limit);
    }

    /**
     * @test
     */
    public function groupBySucceeds()
    {
        $query = new QueryBuilder\Query();
        $groupBy = uniqid();
        $return = $query->GroupBy($groupBy);
        $this->assertEquals(array($groupBy), $query->getGroupBy());
        $this->assertSame($query, $return);
    }

    /**
     * @test
     * @dataProvider validDirectionsDataProvider
     */
    public function orderBySucceeds($direction)
    {
        $query = new QueryBuilder\Query();
        $orderBy = uniqid();
        $return = $query->OrderBy($orderBy, $direction);
        $this->assertEquals(array($orderBy => $direction), $query->getOrderBy());
        $this->assertSame($query, $return);
    }

    /**
     * @test
     * @expectedException tomcroft\tantrum\Exception\QueryException
     */
    public function orderByThrowsQueryException()
    {
        $query = new QueryBuilder\Query();
        $query->OrderBy(uniqid(), uniqid());
    }

    /**
     * @test
     * @dataProvider selectValidDataProvider
     */
    public function selectSucceeds($target, $alias, $fields)
    {
        $query = QueryBuilder\Query::Select($target, $alias, $fields);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Query', get_class($query));
        $this->assertEquals(QueryBuilder\Query::SELECT, $query->getType());
        $this->assertEquals($target, $query->getTarget());
        $this->assertEquals($alias, $query->getAlias());
        $this->assertSame($fields, $query->getFields());
    }

    /**
     * @test
     */
    public function insertSucceeds()
    {
        $target = uniqid();
        $fields = new QueryBuilder\Fields();
        $query = QueryBuilder\Query::Insert($target, $fields);
        $this->assertEquals(QueryBuilder\Query::INSERT, $query->getType());
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Query', get_class($query));
        $this->assertEquals($target, $query->getTarget());
        $this->assertSame($fields, $query->getFields());
    }

    /**
     * @test
     */
    public function deleteSucceeds()
    {
        $target = uniqid();
        $query = QueryBuilder\Query::Delete($target);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Query', get_class($query));
        $this->assertEquals(QueryBuilder\Query::DELETE, $query->getType());
        $this->assertEquals($target, $query->getTarget());
    }

    /**
     * @test
     */
    public function updateSucceeds()
    {
        $target = uniqid();
        $fields = new QueryBuilder\Fields();
        $query = QueryBuilder\Query::Update($target, $fields);
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Query', get_class($query));
        $this->assertEquals(QueryBuilder\Query::UPDATE, $query->getType());
        $this->assertEquals($target, $query->getTarget());
        $this->assertSame($fields, $query->getFields());
    }

    /**
     * @test
     * @dataProvider joinValidDataProvider
     */
    public function innerJoinSucceeds($target, $clauseCollection, $alias = null)
    {
        $query = new QueryBuilder\Query();
        $return = $query->InnerJoin($target, $clauseCollection, $alias);
        $this->assertSame($query, $return);
        $joins = $query->getJoins();
        $this->assertArray($joins);
        $this->assertCount(1, $joins);
        $join = $joins[key($joins)];
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Join', get_class($join));
        $this->assertEquals(QueryBuilder\Join::INNER, $join->getType());
        if(!is_null($alias)) {
            $this->assertEquals($alias, $join->getAlias());
        } else {
            $this->assertTrue(strlen($join->getAlias()) > 0);
        }
        $this->assertSame($clauseCollection, $join->getClauseCollection());
    }

    /**
     * @test
     * @dataProvider joinValidDataProvider
     */
    public function leftJoinSucceeds($target, $clauseCollection, $alias = null)
    {
        $query = new QueryBuilder\Query();
        $return = $query->LeftJoin($target, $clauseCollection, $alias);
        $this->assertSame($query, $return);
        $joins = $query->getJoins();
        $this->assertArray($joins);
        $this->assertCount(1, $joins);
        $join = $joins[key($joins)];
        $this->assertEquals('tomcroft\tantrum\QueryBuilder\Join', get_class($join));
        $this->assertEquals(QueryBuilder\Join::LEFT, $join->getType());
        if(!is_null($alias)) {
            $this->assertEquals($alias, $join->getAlias());
        } else {
            $this->assertTrue(strlen($join->getAlias()) > 0);
        }
        $this->assertSame($clauseCollection, $join->getClauseCollection());
    }

    // Data Providers
    
    public function validTypesDataProvider()
    {
        return array(
            array(QueryBuilder\Query::SELECT),
            array(QueryBuilder\Query::INSERT),
            array(QueryBuilder\Query::UPDATE),
            array(QueryBuilder\Query::DELETE),
        );
    }

    public function invalidLimitDataProvider()
    {
        return array(
            array('10', 25),
            array(10, '25'),
            array(-10, 25),
            array(10, 0),
            array(10, -10),
        );
    }

    public function validDirectionsDataProvider()
    {
        return array(
            array(QueryBuilder\Query::ASC),
            array(QueryBuilder\Query::DESC),
        );
    }

    public function selectValidDataProvider()
    {
        return array(
            array(uniqid(), uniqid(), new QueryBuilder\Fields()),
            array(uniqid(), null, new QueryBuilder\Fields()),
            array(uniqid(), uniqid(), null),
            array(uniqid(), null, null),
        );
    }

    public function joinValidDataProvider()
    {
        return array(
            array(uniqid(), new QueryBuilder\ClauseCollection(), uniqid()),
            array(uniqid(), new QueryBuilder\ClauseCollection(), null),
            array(uniqid(), new QueryBuilder\ClauseCollection()),
        );
    }
}