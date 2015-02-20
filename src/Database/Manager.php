<?php

namespace tantrum\Database;

use tantrum\Exception,
	tantrum\QueryBuilder,
	tantrum\Core;

class Manager extends Core\Module
{
	const DRIVER_MYSQL = 'mysql';

	private static $supportedDrivers = array(
		self::DRIVER_MYSQL,
	);
	private static $self; 
	private static $connections = array();

	public static function init()
	{
		if(is_null(self::$self)) {
			$self = new Manager();
		}
		return $self;
	}
	
	public static function getConnection($driver, $schema)
	{
		$connectionKey = sprintf('%s-%s', $driver, $schema);

		if(!array_key_exists($connectionKey, self::$connections)) {
			$config = self::getConfigOption($driver);;

			$dataSourceName = sprintf('%s:host=%s;dbname=%s', $driver, $config['host'], $schema);
			$options = array(
				\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
			); 
			try {
				$pdoConnection = self::newInstance('PDO', $dataSourceName, $config['user'], $config['password'], $options);
			} catch(\PDOException $e) {
				self::parseException($e);
			}

			$adaptor = self::newInstance(sprintf('tantrum_%s_adaptor', $driver));

			$connection = self::newInstance('tantrum\Database\Connection');
			$connection->setPdoConnection($pdoConnection);
			$connection->setAdaptor($adaptor);
			$connection->setSchema($schema);

			self::$connections[$connectionKey] = $connection;
		} else {
			$connection = self::$connections[$connectionKey];
		}

		return $connection;
	}

	protected static function parseException(\PDOException $e)
	{
		// TODO: These are probably adaptor specific error codes
		switch($e->getCode()) {
			case 1049:
			case 2002:
			case 1045:
				throw new Exception\DatabaseException($e->getMessage());
			default:
				throw new Exception\DatabaseException('An unhandled database error has occurred: '.$e->getMessage());
		}
	}

	public static function isSupported($driver)
	{
		if(!in_array($driver, self::$supportedDrivers)) {
			throw new Exception\DatabaseException($driver.' is not supported');
		}
	}
}
