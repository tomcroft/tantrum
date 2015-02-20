<?php

namespace tantrum\Database;

use tantrum\Core,
	tantrum\Exception,
	tantrum\QueryBuilder;

class Entity extends Core\Module 
{
	private   $initialised = false;
	protected $connection;
	protected $columns = array();
	protected $objects = array();
	protected $table;
	protected $primary;
	
	private $autoSet;
	
	public function setTable($table)
	{
		$this->table = $table;
	}

	public function setConnection(Connection $connection)
	{
		$this->connection = $connection;
	}
	
	public function __set($key, $value)
	{
		$this->init();
		$key = $this->callListener('mapColumnName', $key);
		if(!array_key_exists($key, $this->columns)) {
			throw new Exception\EntityException($key.' does not exist on this entity: '.print_r($value, 1), E_USER_NOTICE);
		} else {
			$this->columns[$key]->setValue($value);
		}
	}
	
	public function __get($key)
	{
		$this->init();
		$key = $this->callListener('mapColumnName', $key);
		if(!array_key_exists($key, $this->columns)) {
			throw new Exception\EntityException('Variable '.$key.' does not exist on this entity.', E_USER_NOTICE);
		}
		return $this->columns[$key]->GetValue();
	}
	
	public function __call($key, $filter = array())
	{
		$this->init();
		if(array_key_exists($key, $this->objects) && is_callable($this->objects[$key])) {
			return call_user_func_array($this->objects[$key], array($this->columns[$key]->getColumnName(), $this->columns[$key]->getValue()));
		} else {
			throw new Exception\EntityException('Function '.$key.' does not exist on this entity.', E_USER_NOTICE);
		}
	}

	public function save()
	{
		$this->init();
		if($this->isModified()) {
			$primaryKey = $this->primary->getValue();	
			if(empty($primaryKey)) {
				return $this->create();
			} else {
				return $this->update();
			}
		}
		return false;
	}
	
	protected function create()
	{
		$this->init();
		$query = QueryBuilder\Query::Insert($this->getHandle(), $this->getFieldObject());
		$this->connection->query($query);
		// TODO: This should be settable from outside
		$this->primary->setValue($this->connection->getInsertId());
		$this->resetModified();
		
		return true;
	}
	
	protected function update()
	{
		$this->init();
		$query = QueryBuilder\Query::Update($this->getHandle(), $this->GetFieldObject())
			->Where($this->primary->getColumnName(), $this->primary->getValue());
		$this->connection->query($query);
		$this->resetModified();
		
		return true;
	}
	
	public function loadByKey($key, $value)
	{
		$this->init();
		$this->autoSet = true;
		$query = QueryBuilder\Query::Select($this->getHandle(), NULL, $this->getFieldObject())
			->Where($key, $value);
		$this->connection->query($query);
		foreach($this->connection->fetch() as $key => $value) {
			$this->$key = $value;
		}
		return true;
	}
	
	public function isModified()
	{
		$this->init();
		foreach($this->columns as $columnName => $column) {
			if($column->isModified() === true) {
				return true;
			}
		}
		return false;
	}
	
	protected function getColumnDefinitions()
	{
		$key = __CLASS__.'::ColumnDefinitions('.$this->getHandle().')';
		$columns = $this->getFromCache($key);

		if(is_null($columns)) {
			$columns = $this->connection->getColumnDefinitions($this->table);
			$this->setInCache($key, $columns);
		}
		
		foreach($columns as $column) {

			$this->columns[$key = $this->callListener('mapColumnName', $column->getColumnName())] = $column;

			if(!is_null($column->getJoinSchema())) {
				$this->objects[$this->callListener('mapColumnName', $column->getColumnName())] = function($key, $value) {
					$entity = Entity::get($this->columns[$key]->getJoinSchema());
					$entity->loadByKey($this->columns[$key]->getJoinOn(), $value);
					return $entity;
				};
			}

			if($column->isPrimary()) {
				$this->primary = $column;
			}
		}
	}
	
	protected function getFieldObject()
	{
		$this->init();
		$fieldCollection = new QueryBuilder\Fields();
		foreach($this->columns as $key => $field) {
			$columnName = $field->getColumnName();
			$fieldCollection->$columnName = $field->getValue();
		}
		return $fieldCollection;
	}
	
	protected function resetModified()
	{
		$this->init();
		foreach($this->columns as $field) {
			$field->setModified(false);
		}
	}

	protected function init()
	{
		if($this->initialised === false) {
			$this->getColumnDefinitions($this->table);
			$this->initialised = true;
		}	
	}

	protected function getHandle()
	{
		return sprintf('%s.%s', $this->connection->getSchema(), $this->table);
	}
}
