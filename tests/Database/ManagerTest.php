<?php

namespace tantrum\tests\Database;

use tantrum\tests,
    tantrum\Core,
    tantrum\QueryBuilder,
    tantrum\Database;

/**
 * @runTestsInSeparateProcesses
 */
class ManagerTest extends tests\TestCase
{
    /**
     * @test
     */
    public function getConnectionSucceeds()
    {
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $connection = $this->mock('tantrum\Database\Connection');
        $connection->shouldReceive('setPdoConnection')
            ->once()
            ->with($pdo)
            ->andReturn(true);
        $connection->shouldReceive('setAdaptor')
            ->once()
            ->with($adaptor)
            ->andReturn(true);
        $connection->shouldReceive('setSchema')
            ->once()
            ->with('main')
            ->andReturn(true);

        $result = Database\Manager::getConnection('mysql', 'main');
        $this->assertSame($connection, $result);

        $result = Database\Manager::getConnection('mysql', 'main');
        $this->assertSame($connection, $result);
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     */
    public function getConnectionCallsParseException()
    {
        $pdo = $this->mockPDO();
        $pdo->shouldReceive('__construct')
            ->once()
            ->andThrow(new \PDOException('TestMessage', 1049));

        $adaptor = $this->mock('tantrum_mysql_adaptor');

        $connection = $this->mock('tantrum\Database\Connection');
        $connection->shouldReceive('setPdoConnection')
            ->once()
            ->with($pdo)
            ->andReturn(true);
        $connection->shouldReceive('setAdaptor')
            ->once()
            ->with($adaptor)
            ->andReturn(true);
        $connection->shouldReceive('setSchema')
            ->once()
            ->with('main')
            ->andReturn(true);

        $result = Database\Manager::getConnection('mysql', 'main');
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     */
    public function getThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $manager = Database\Manager::get('database-does-not-exist');
    }

    /**
     * @test
     */
    public function getColumnDefinitionsSucceeds()
    {
        $this->markTestIncomplete();
        $database            = 'main';
        $table               = 'user';
        $expectedQueryString = uniqid();
        $expectedParameters  = array('one' => 'two');
        $expectedResult      = array('three' => 'four');

        $query = $this->mock('tantrum\QueryBuilder\Query');
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $adaptor->shouldReceive('getColumnDefinitions')
            ->once()
            ->with($database, $table)
            ->andReturn($query);
        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $statement = $this->mock('stdClass');
        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedResult);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));

        $pdo = $this->mockPDO();
        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $manager = Database\Manager::get($database);
        $result = $manager->getColumnDefinitions($database, $table);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @test
     */
    public function queryWithSelectSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));

        $this->assertTrue($manager->query($query));
    }

    /**
     * @test
     */
    public function queryWithInsertSucceeds()
    {
        $this->markTestIncomplete();
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::INSERT);
        $query->shouldReceive('getFields')
            ->once()
            ->andReturn(new QueryBuilder\Fields);
        $query->shouldReceive('getDuplicateFieldsForUpdate')
            ->once()
            ->andReturn(null);

        $adaptor->shouldReceive('formatInsert')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with(array())
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));

        $this->assertTrue($manager->query($query));
    }

    /**
     * @test
     */
    public function queryWithDeleteSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::DELETE);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatDelete')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));

        $this->assertTrue($manager->query($query));
    }

    /**
     * @test
     */
    public function queryWithUpdateSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::UPDATE);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);
        $query->shouldReceive('getFields')
            ->once()
            ->andReturn(new QueryBuilder\Fields);

        $adaptor->shouldReceive('formatUpdate')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));

        $this->assertTrue($manager->query($query));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage Query Type Not Handled
     */
    public function queryWithWrongQueryTypeThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');

        $query = $this->mock('tantrum\QueryBuilder\Query');
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(uniqId());

        $manager = Database\Manager::get('main');
        $manager->query($query);

    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage TestMessage
     */
    public function pdoExceptionIsCaughtAndTurnedIntoDatabaseException()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $pdo->shouldReceive('setAttribute')
            ->andThrow(new \PDOException('TestMessage'));

        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $manager->query($query);
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     */
    public function preparedStatementFailsAndThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn(false);
        $pdo->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array());

        $manager->query($query);
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage ExceptionMessage
     */
    public function checkErrorsThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(1, null, 'ExceptionMessage'));

        $manager->query($query);
    }

    /**
     * @test
     */
    public function getInsertIdSucceds()
    {
        $this->markTestIncomplete();
        $expected = 9;

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $pdo->shouldReceive('lastInsertId')
            ->once()
            ->andReturn($expected);

        $this->assertEquals($expected, $manager->getInsertId());
    }

    /**
     * @test
     */
    public function getAffectedRowsSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedRows = 9;

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::UPDATE);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);
        $query->shouldReceive('getFields')
            ->once()
            ->andReturn(new QueryBuilder\Fields);

        $adaptor->shouldReceive('formatUpdate')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('rowCount')
            ->once()
            ->andReturn($expectedRows);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedRows, $manager->getAffectedRows());
    }

    /**
     * @test
     */
    public function fetchAllWithoutClassSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(2)
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetchAll());
    }

    /**
     * @test
     */
    public function fetchAllWithClassNoConstructorArgsSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(1048584, 'tantrum\Database\Entity', array())
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetchAll('tantrum\Database\Entity'));
    }

    /**
     * @test
     */
    public function fetchAllWithClassAndConstructorArgsSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(1048584, 'tantrum\Database\Entity', array('one' => 'two'))
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetchAll('tantrum\Database\Entity', array('one' => 'two')));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage Constructor arguments passed without a class name
     */
    public function fetchAllWithNoClassAndConstructorArgsThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $manager->fetchAll('', array('one' => 'two'));
    }

    /**
     * @test
     */
    public function fetchWithoutClassSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(2)
            ->andReturn(true);
        $statement->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetch());
    }

    /**
     * @test
     */
    public function fetchWithClassSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(1048584, 'tantrum\Database\Entity', array())
            ->andReturn(true);
        $statement->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetch('tantrum\Database\Entity'));
    }

    /**
     * @test
     */
    public function fetchWithClassAndConstructorArgsSucceeds()
    {
        $this->markTestIncomplete();
        $expectedParameters  = array('one' => 'two');
        $expectedQueryString = uniqid();
        $expectedReturn      = array('three' => 'four');

        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $query = $this->mock('tantrum\QueryBuilder\Query');
        $statement = $this->mock('stdClass');
        
        $query->shouldReceive('getType')
            ->once()
            ->andReturn(QueryBuilder\Query::SELECT);
        $query->shouldReceive('getParameters')
            ->once()
            ->andReturn($expectedParameters);

        $adaptor->shouldReceive('formatSelect')
            ->once()
            ->with($query)
            ->andReturn($expectedQueryString);

        $pdo->shouldReceive('setAttribute')
            ->once()
            ->andReturn(true);
        $pdo->shouldReceive('prepare')
            ->once()
            ->with($expectedQueryString)
            ->andReturn($statement);

        $statement->shouldReceive('execute')
            ->once()
            ->with($expectedParameters)
            ->andReturn(true);
        $statement->shouldReceive('errorInfo')
            ->once()
            ->andReturn(array(0));
        $statement->shouldReceive('setFetchMode')
            ->once()
            ->with(1048584, 'tantrum\Database\Entity', array('one' => 'two'))
            ->andReturn(true);
        $statement->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetch('tantrum\Database\Entity', array('one' => 'two')));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage Constructor arguments passed without a class name
     */
    public function fetchWithNoClassAndConstructorArgsThrowsDatabaseException()
    {
        $this->markTestIncomplete();
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $manager->fetch('', array('one' => 'two'));
    }

    // Utils

    protected function mockPDO()
    {
        $mock = \Mockery::mock('tantrum\tests\MockPDO', array());
        return Core\Container::injectInstance('PDO', $mock);
    }

    public function setUp()
    {
        $host      = 'localhost';
        $schema    = 'main';
        $user      = 'root';
        $password  = '';

        $config = Core\Config::init();
        $config->setDatabase('mysql', $host, $schema, $user, $password);
    }
}