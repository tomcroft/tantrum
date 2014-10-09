<?php

namespace tomcroft\tantrum\Entity;

use tomcroft\tantrum\Exception;

class Entity 
{

	protected $dB;
	protected $columns = array();
	protected $objects = array();
	protected $handle;
	protected $primary;
	
	private $autoSet;
	
	public function __construct($handle, $autoSet=false)
	{
		$this->autoSet = $autoSet;
		$this->handle = $handle;
		list($schema, $table) = explode('.', $handle);
		$this->dB = $GLOBALS['objConfig']->DB($schema);	
		$this->GetColumnDefinitions();
	}
	
	public function __set($key, $value)
	{
		$key = $this->dB->MapColumnName($key);
		if(!array_key_exists($key, $this->columns))
		{
			throw new EntityException($strKey.' does not exist on this entity: '.print_r($mxdValue, 1), E_USER_NOTICE);
		}
		elseif($this->columns[$key]->IsPrimary() && !$this->autoSet)
		{
			throw new EntityException('Setting a primary key: '.$key, E_USER_NOTICE);
		}
		else
		{
			$this->arrColumns[$key]->SetValue($value);
		}
	}
	
	public function __get($key)
	{
		$key = $this->dB->MapColumnName($key);
		if(!array_key_exists($key, $this->columns))
		{
			throw new EntityException('Variable '.$key.' does not exist on this entity.', E_USER_NOTICE);
		}
		return $this->arrColumns[$key]->GetValue();
	}
	
	public function __call($key, $filter = array())
	{
		if(is_callable($this->objects[$key]))
		{
			return call_user_func_array($this->objects[$key], array($this->columns[$key]->GetColumnName(), $this->columns[$key]->GetValue()));
		}
		else
		{
			throw new EntityException('Function '.$key.' does not exist on this entity.', E_USER_NOTICE);
		}
	}

	public function Save($bolDeep=false)
	{
		if($this->IsModified())
		{
			if(!is_numeric($this->primary->GetValue()))
			{
				return $this->Create();
			}
			else
			{
				return $this->Update();
			}
			if($bolDeep === true)
			{
				//$this->SaveEntities();
			}
		}
	}
	
	protected function Create()
	{
		$query = Query::Insert($this->handle, null,
			$this->GetFieldObject());
		$this->dB->Query($query);
		$this->objPrimary->SetValue($this->objDB->GetInsertId());
		$this->ResetModified();
		
		$this->Cache();
		return true;
	}
	
	protected function Update()
	{
		$query = Query::Update($this->handle, null,
			$this->GetFieldObject())
			->Where($this->primary->getColumnName(), $this->primary->GetValue());
		$this->objDB->Query($query);
		$this->ResetModified();
		$this->Cache();
		return true;
	}
	
	public function LoadByKey($key, $value)
	{
		//Could GetFromCache use a subclassed pdo object????one!one!
		$this->autoSet = true;
		$query = Query::Select($this->handle, NULL, $this->GetFieldObject())
			->Where($key, $value);
		$this->dB->Query($query);
		foreach($this->dB->Fetch() as $key => $value)
		{
			$this->$key = $value;
		}
		
	}
	
	public function IsModified()
	{
		foreach($this->columns as $columnName => $column)
		{
			if($column->IsModified() === true)
			{
				return true;
			}
		}
		return false;
	}
	
	protected function GetColumnDefinitions()
	{
		$key = __CLASS__.'::ColumnDefinitions.'.$this->handle;
		$columns = $GLOBALS['objConfig']->Cache->Get($key);
		list($schema, $table) = explode('.', $this->handle);
		if(!$columns)
		{
			$columns = $this->dB->GetColumnDefinitions($table);
			$GLOBALS['objConfig']->Cache->Set($key, $columns);
		}
		
		foreach($columns as $column)
		{
			$this->columns[$this->dB->MapColumnName($column->GetColumnName())] = $column;
			if(!is_null($column->GetJoinSchema()))
			{
				$this->objects[$this->dB->MapColumnName($column->GetColumnName())] = function($key, $value)
				{
					$entity = new Entity($this->columns[$key]->GetJoinSchema());
					$entity->LoadByKey($this->columns[$key]->GetJoinOn(), $value);
					return $entity;
				};
			}
			if($column->IsPrimary())
			{
				$this->primary = $column;
			}
		}
	}
	
	protected function GetFieldObject()
	{
		$fieldCollection = new Fields();
		foreach($this->columns as $key => $field)
		{
			$columnName = $field->GetColumnName();
			$fieldCollection->$columnName = $field->GetValue();
		}
		return $fieldCollection;
	}
	
	protected function ResetModified()
	{
		foreach($this->columns as $field)
		{
			$field->SetModified(false);
		}
	}
	
	protected function Cache()
	{
		$cache = array();
		foreach($this->columns as $key => $field)
		{
			$cache[$key] = $field->GetValue();
		}
	}
}
