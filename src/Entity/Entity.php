<?php

namespace tantrum\Entity;

use tantrum\Core,
	tantrum\Database,
	tantrum\Exception,
	tantrum\QueryBuilder;

class Entity extends Core\Module 
{
	private   $initialised = false;
	protected $dB;
	protected $columns = array();
	protected $objects = array();
	protected $handle;
	protected $schema;
	protected $table;
	protected $primary;
	
	private $autoSet;

	public static function get($handle)
	{
		$entity = self::newInstance('tantrum\Entity\Entity');

		$entity->setHandle($handle);
		return $entity;
	}
	
	public function setHandle($handle)
	{
		$this->validateHandle($handle);
		list($this->schema, $this->table) = explode('.', $handle);
		$this->handle = $handle;                                                        
	}

	public function getHandle()
	{
		return $this->handle;
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
		$query = QueryBuilder\Query::Insert($this->handle, $this->getFieldObject());
		$this->dB->query($query);
		// TODO: This should be settable from outside
		$this->primary->setValue($this->dB->getInsertId());
		$this->resetModified();
		
		return true;
	}
	
	protected function update()
	{
		$this->init();
		$query = QueryBuilder\Query::Update($this->handle, $this->GetFieldObject())
			->Where($this->primary->getColumnName(), $this->primary->getValue());
		$this->dB->query($query);
		$this->resetModified();
		
		return true;
	}
	
	public function loadByKey($key, $value)
	{
		$this->init();
		$this->autoSet = true;
		$query = QueryBuilder\Query::Select($this->handle, NULL, $this->getFieldObject())
			->Where($key, $value);
		$this->dB->query($query);
		foreach($this->dB->fetch() as $key => $value) {
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
		$key = __CLASS__.'::ColumnDefinitions('.$this->handle.')';
		$columns = $this->getFromCache($key);
		if(is_null($columns)) {
			$columns = $this->dB->getColumnDefinitions($this->schema, $this->table);
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

	protected function validateHandle($handle)
	{
		if(!is_string($handle) || count(explode('.', $handle)) !== 2) {
			throw new Exception\EntityException('Handle must be a dot separated string'); 
		}
		return true;
	}

	protected function init()
	{
		if($this->initialised === false) {
			$manager = $this->newInstance('tantrum\Database\Manager');
			$this->dB = $manager::get($this->schema);
			$this->getColumnDefinitions();
			$this->initialised = true;
		}	
	}
}
