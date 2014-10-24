<?php

namespace tantrum\QueryBuilder;

class Field
{
	protected $columnName = null;
	protected $required = null;
	protected $maximumLength = null;
	protected $columnKey = null;
	protected $joinDatabase = null;
	protected $joinTable = null;
	protected $joinOn = null;
	protected $modified = false;
	protected $hasExternalReferences = null;
	protected $ordinalPosition = null;
	protected $positionINUniqueConstraint = null;
	protected $value = null;
	
	public function setValue($value)
	{
		$this->value = $value;
		$this->modified = true;
	}
	
	public function isPrimary()
	{
		return $this->columnKey === 'PRI';
	}
	
	public function isModified()
	{
		return $this->modified;
	}
	
	public function getJoinSchema()
	{
		if(!is_null($this->joinDatabase) && !is_null($this->joinTable)) {
			return sprintf('%s.%s', $this->joinDatabase, $this->joinTable);
		}
		return null;
	}
	
	public function getJoinOn()
	{
		return $this->joinOn;
	}
	
	public function getColumnName()
	{
		return $this->columnName;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function setModified($modified)
	{
		$this->modified = $modified;
	}
}
