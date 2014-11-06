<?php

namespace tantrum\Database;

use tantrum\Exception\DatabaseException,
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
	protected $statement;
	protected $primaryKey;
	protected $fields = array();
	protected $joins = array();
	protected $data = array();
	
	public static function Get($schema)
	{
		$driver = self::getConfigOption('databaseDriver');
		$dataSourceName = $driver.':host='.self::getConfigOption('databaseHost').';dbname='.$schema;
		$options = array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 
		try {
			self::$connection = new \PDO($dataSourceName, self::getConfigOption('databaseUser'), self::getConfigOption('databasePassword'), $options);
		} catch(\PDOException $e) {
			self::rethrow($e);
		}

		$className = 'tantrum\\'.strtolower($driver);
		$adaptor = $this->newInstance($className());
		$adaptor->setConnection($this->connection);

	}
	
	public function Query(Query $query)
	{
		switch ($query->getType()) {
			case Query::SELECT: 
				$query = $this->FormatSelect($query);
				$parameters = $query->GetParameters();
				break;
			case Query::INSERT:
				$queryString = $this->FormatInsert($query);
				$parameters = array_values($query->getFields()->ToArray());
				$parameters = !is_null($query->GetDuplicateFieldsForUpdate())
					? array_merge($parameters, array_values($query->GetDuplicateFieldsForUpdate()->ToArray()))
					: $parameters;
				break;
			case Query::DELETE:
				$queryString = $this->FormatDelete($query);
				$parameters = $query->GetParameters();
				break;
			case Query::UPDATE:
				$query = $this->FormatUpdate($query);
				$parameters = array_merge(array_values($query->getFields()->ToArray()),$query->GetParameters());
				break;			
			default:
				throw new DatabaseException('Query Type Not Handled');
				break;
		} try {
			$this->connection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); 
			if($this->statement = $this->connection->prepare($queryString)) {
				$this->statement->execute($parameters);
				return $this->CheckErrors($this->statement, $queryString);
			} else {
				//$this->connection->debugDumpParams();
				throw new DatabaseException("Prepare statement failed:\r\n".print_r($this->connection->errorInfo(), 1)."\r\n".$queryString);
			}
		} catch(PDOException $e) {
			throw new DatabaseException($e->getMessage());
		}
	}
	
	public function GetInsertId()
	{
		return $this->connection->lastInsertId(); 
	}
	
	public function GetAffectedRows()
	{
		return $this->statement->rowCount();
	}
	
	protected function GetConnection(){
		if(isset($this->connection)){
			return $this->connection;
		} else {
			$connection = $this->connection = $this->Connect();
		}
		return $connection;
	}
	
	public function Fetch($className=null, $constructorArgs=array())
	{
		if(!is_null($className))
		{
			$this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
		} else {
			$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->statement->fetch();
		
	}
	
	public function FetchAll($className=null, $constructorArgs=array())
	{
		if(!is_null($className))
		{
			$this->statement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $className, $constructorArgs);
		} else {
			$this->statement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->statement->fetchAll();
	}
	
	protected function CheckErrors($pdo, $queryString)
	{
		$errors = $pdo->errorInfo();
		if($errors[0] > 0) {
			throw new DatabaseException($errors[2].":\r\n".$queryString);
		}
	}

	protected function reThrow(\PDOException $e)
	{
		switch($e->getCode()) {
			case 2002:
				$ex = new DatabaseException('Could not connect to database.');
			break;
		}
		throw $ex;
	}
}
