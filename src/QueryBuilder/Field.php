<?php

namespace tomcroft\tantrum\QueryBuilder;

class Field
{
	protected $columnName = NULL;
	protected $required = NULL;
	protected $maximumLength = NULL;
	protected $columnKey = NULL;
	protected $joinDatabase = NULL;
	protected $joinTable = NULL;
	protected $joinOn = NULL;
	protected $extensionColumn = NULL; //whatever this means!
	protected $modified = FALSE;
	protected $hasExternalReferences = NULL; //whatever this means!
	protected $ordinalPosition = NULL;
	protected $positionInUniqueConstraint = NULL;
	protected $mvalue = NULL;
	
	public function SetValue($value)
	{
		$this->value = $value;
		$this->modified = true;
	}
	
	public function IsPrimary()
	{
		return $this->columnKey === 'PRI';
	}
	
	public function IsModified()
	{
		return $this->modified;
	}
	
	public function GetJoinSchema()
	{
		if(!is_null($this->joinDatabase) && !is_null($this->joinTable))
		{
			return sprintf('%s.%s', $this->joinDatabase, $this->joinTable);
		}
		return null;
	}
	
	public function GetJoinOn()
	{
		return $this->joinOn;
	}
	
	public function GetColumnName()
	{
		return $this->columnName;
	}
	
	public function GetValue()
	{
		return $this->value;
	}
	
	public function SetModified($modified)
	{
		$this->modified = $modified;
	}
}
