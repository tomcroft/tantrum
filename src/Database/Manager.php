<?php

namespace tantrum\Database;

use tantrum\Exception,
	tantrum\QueryBuilder,
	tantrum\Core;

class Manager extends Core\Module
{
	const KEY_PRIMARY = 'PRI';
	const KEY_FOREIGN = 'FOREIGN';
	const KEY_UNIQUE = 'UNIQUE';
	
	const REL_TYPE_ONE_TO_ONE = '121';
	const REL_TYPE_ONE_TO_MANY = '12n';
	const REL_TYPE_MANY_TO_MANY = 'n2n';
	
	const TYPE_STRING = 'string';
	const TYPE_INTEGER = 'integer';

	protected static $connection;
	public static $adaptor;

	protected $statement;
	protected $primaryKey;
	protected $fields = array();
	protected $joins = array();
	protected $data = array();
	
	public static function get($schema)
	{
		$driver = strtolower(self::getConfigOption('databaseDriver'));
		$dataSourceName = $driver.':host='.self::getConfigOption('databaseHost').';dbname='.$schema;
		$options = array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 
		try {
			self::$connection = self::newInstance('PDO', $dataSourceName, self::getConfigOption('databaseUser'), self::getConfigOption('databasePassword'), $options);
		} catch(\PDOException $e) {
			self::parseException($e);
		}

		$className = sprintf('tantrum_%s_adaptor', $driver);
		self::$adaptor = self::newInstance($className);
		return self::newInstance(__CLASS__);
	}

	public function getColumnDefinitions($schema, $table)
	{
		$query = self::$adaptor->getColumnDefinitions($schema, $table);
		$this->query($query);
		$fields = $this->fetchAll('tantrum\QueryBuilder\Field');
		return $fields;
	}
	
	public function query(QueryBuilder\Query $query)
	{
		$adaptor = self::$adaptor;
		switch ($query->getType()) {
			case QueryBuilder\Query::SELECT: 
				$queryString = $adaptor::formatSelect($query);
				$parameters = $query->getParameters();
				break;
			case QueryBuilder\Query::INSERT:
				$queryString = $adaptor::formatInsert($query);
				$parameters = array_values($query->getFields()->toArray());
				$parameters = !is_null($query->getDuplicateFieldsForUpdate())
					? array_merge($parameters, array_values($query->getDuplicateFieldsForUpdate()->toArray()))
					: $parameters;
				break;
			case QueryBuilder\Query::DELETE:
				$queryString = $adaptor::formatDelete($query);
				$parameters = $query->getParameters();
				break;
			case QueryBuilder\Query::UPDATE:
				$queryString = $adaptor::formatUpdate($query);
				$parameters = array_merge(array_values($query->getFields()->toArray()), $query->getParameters());
				break;
			default:
				throw new Exception\DatabaseException('Query Type Not Handled');
		} try {
			self::$connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); 
			if($this->statement = self::$connection->prepare($queryString)) {
				$this->statement->execute($parameters);
				return $this->checkErrors($this->statement, $queryString);
			} else {
				//$this->connection->debugDumpParams();
				throw new Exception\DatabaseException("Prepare statement failed:\r\n".print_r(self::$connection->errorInfo(), 1)."\r\n".$queryString);
			}
		} catch(\PDOException $e) {
			throw new Exception\DatabaseException($e->getMessage());
		}
	}
	
	public function getInsertId()
	{
		return self::$connection->lastInsertId(); 
	}
	
	public function getAffectedRows()
	{
		return $this->statement->rowCount();
	}
	
	public function fetch($className=null, $constructorArgs=array())
	{
		if(!empty($className))
		{
			$this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
		} else {
			if(count($constructorArgs) > 0) {
				throw new Exception\DatabaseException('Constructor arguments passed without a class name');
			}
			$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->statement->fetch();
		
	}
	
	public function fetchAll($className=null, $constructorArgs=array())
	{
		if(!empty($className))
		{
			$this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
		} else {
			if(count($constructorArgs) > 0) {
				throw new Exception\DatabaseException('Constructor arguments passed without a class name');
			}
			$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->statement->fetchAll();
	}
	
	protected function checkErrors($pdo, $queryString)
	{
		$errors = $pdo->errorInfo();
		if($errors[0] > 0) {
			throw new Exception\DatabaseException($errors[2].":\r\n".$queryString);
		}

		return true;
	}

	protected function parseException(\PDOException $e)
	{
		// TODO: These are probably adaptor specific error codes
		switch($e->getCode()) {
			case 1049:
			case 2002:
				$ex = new Exception\DatabaseException($e->getMessage());
			default:
				$ex = new Exception\DatabaseException('An unhandled database error has occurred: '.$e->getMessage());
			break;
		}
		throw $ex;
	}
}
