<?php

namespace TomChaton\clingDB\QueryBuilder;

class Field
{
	protected $strColumnName = NULL;
	protected $bolRequired = NULL;
	protected $intMaximumLength = NULL;
	protected $strColumnKey = NULL;
	protected $strJoinDatabase = NULL;
	protected $strJoinTable = NULL;
	protected $strJoinOn = NULL;
	protected $bolExtensionColumn = NULL; //whatever this means!
	protected $bolModified = FALSE;
	protected $bolHasExternalReferences = NULL; //whatever this means!
	protected $intOrdinalPosition = NULL;
	protected $intPositionInUniqueConstraint = NULL;
	protected $mxdValue = NULL;
	
	public function SetValue($mxdValue)
	{
		$this->mxdValue = $mxdValue;
		$this->bolModified = true;
	}
	
	public function IsPrimary()
	{
		return $this->strColumnKey === 'PRI';
	}
	
	public function IsModified()
	{
		return $this->bolModified;
	}
	
	public function GetJoinSchema()
	{
		if(!is_null($this->strJoinDatabase) && !is_null($this->strJoinTable))
		{
			return sprintf('%s.%s', $this->strJoinDatabase, $this->strJoinTable);
		}
		return null;
	}
	
	public function GetJoinOn()
	{
		return $this->strJoinOn;
	}
	
	public function GetColumnName()
	{
		return $this->strColumnName;
	}
	
	public function GetValue()
	{
		return $this->mxdValue;
	}
	
	public function SetModified($bolModified)
	{
		$this->bolModified = $bolModified;
	}
}
?>
