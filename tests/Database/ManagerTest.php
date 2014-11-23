<?php

namespace tantrum\tests;

use tantrum\Core,
    tantrum\QueryBuilder,
    tantrum\Database;

/**
 * @runTestsInSeparateProcesses
 */
class ManagerTest extends TestCase
{
    /**
     * @test
     */
    public function getSucceeds()
    {
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');

        $manager = Database\Manager::get('main');
        $this->assertTrue($manager instanceof Database\Manager);

        $reflectionManager = new \ReflectionClass($manager);
        $reflectionAdaptor = $reflectionManager->getProperty('adaptor');

        $this->assertEquals($reflectionAdaptor->getValue(), $adaptor);
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     */
    public function getThrowsDatabaseException()
    {
        $manager = Database\Manager::get('database-does-not-exist');
    }

    /**
     * @test
     */
    public function getColumnDefinitionsSucceeds()
    {
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
            ->with(1048584, 'tantrum\Entity\Entity', array())
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetchAll('tantrum\Entity\Entity'));
    }

    /**
     * @test
     */
    public function fetchAllWithClassAndConstructorArgsSucceeds()
    {
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
            ->with(1048584, 'tantrum\Entity\Entity', array('one' => 'two'))
            ->andReturn(true);
        $statement->shouldReceive('fetchAll')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetchAll('tantrum\Entity\Entity', array('one' => 'two')));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage Constructor arguments passed without a class name
     */
    public function fetchAllWithNoClassAndConstructorArgsThrowsDatabaseException()
    {
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
            ->with(1048584, 'tantrum\Entity\Entity', array())
            ->andReturn(true);
        $statement->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetch('tantrum\Entity\Entity'));
    }

    /**
     * @test
     */
    public function fetchWithClassAndConstructorArgsSucceeds()
    {
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
            ->with(1048584, 'tantrum\Entity\Entity', array('one' => 'two'))
            ->andReturn(true);
        $statement->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedReturn);

        $this->assertTrue($manager->query($query));
        $this->assertEquals($expectedReturn, $manager->fetch('tantrum\Entity\Entity', array('one' => 'two')));
    }

    /**
     * @test
     * @expectedException tantrum\Exception\DatabaseException
     * @expectedExceptionMessage Constructor arguments passed without a class name
     */
    public function fetchWithNoClassAndConstructorArgsThrowsDatabaseException()
    {
        $pdo = $this->mockPDO();
        $adaptor = $this->mock('tantrum_mysql_adaptor');
        $manager = Database\Manager::get('main');
        $manager->fetch('', array('one' => 'two'));
    }

    // Utils

    protected function mockPDO()
    {
        $mock = \Mockery::mock('mockPDO', array());
        return Core\Container::injectInstance('PDO', $mock);
    }

    public function setUp()
    {
        $configs = array(
            'databaseDriver'   => 'mysql',
            'databaseHost'     => 'localhost',
            'defaultSchema'    => 'main',
            'databaseUser'     => 'root',
            'databasePassword' => '',
        );
        $config = Core\Config::init();
        $config->set($configs);
    }
}