<?php

namespace tomcroft\tantrum\QueryBuilder;

class Field
{
	protected $columnName = NULL;
	protected $columnKey = NULL;
	protected $joinDatabase = NULL;
	protected $joinTable = NULL;
	protected $joinOn = NULL;
	protected $modified = FALSE;
	protected $value = NULL;
	
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
