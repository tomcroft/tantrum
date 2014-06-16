<?php

namespace TomChaton\clingDB\Entity;

use TomChaton\clingDB\Exception;

class Entity 
{

	protected $objDB;
	protected $arrColumns = array();
	protected $arrObjects = array();
	protected $strHandle;
	protected $objPrimary;
	
	private $bolAutoSet;
	
	public function __construct($strHandle, $bolAutoSet=false)
	{
		$this->bolAutoSet = $bolAutoSet;
		$this->strHandle = $strHandle;
		list($strSchema, $strTable) = explode('.', $strHandle);
		$this->objDB = $GLOBALS['objConfig']->DB($strSchema);	
		$this->GetColumnDefinitions();
	}
	
	public function __set($strKey, $mxdValue)
	{
		$strKey = $this->objDB->MapColumnName($strKey);
		if(!array_key_exists($strKey, $this->arrColumns))
		{
			throw new Exception($strKey.' does not exist on this entity: '.print_r($mxdValue, 1), E_USER_NOTICE);
		}
		elseif($this->arrColumns[$strKey]->IsPrimary() && !$this->bolAutoSet)
		{
			throw new Exception('Setting a primary key: '.$strKey, E_USER_NOTICE);
		}
		else
		{
			$this->arrColumns[$strKey]->SetValue($mxdValue);
		}
	}
	
	public function __get($strKey)
	{
		$strKey = $this->objDB->MapColumnName($strKey);
		if(!array_key_exists($strKey, $this->arrColumns))
		{
			throw new Exception('Variable '.$strKey.' does not exist on this entity.', E_USER_NOTICE);
		}
		return $this->arrColumns[$strKey]->GetValue();
	}
	
	public function __call($strKey, $arrFilter = array())
	{
		if(is_callable($this->arrObjects[$strKey]))
		{
			return call_user_func_array($this->arrObjects[$strKey], array($this->arrColumns[$strKey]->GetColumnName(), $this->arrColumns[$strKey]->GetValue()));
		}
		else
		{
			throw new Exception('Function '.$strKey.' does not exist on this entity.', E_USER_NOTICE);
		}
	}

	public function Save($bolDeep=false)
	{
		if($this->IsModified())
		{
			if(!is_numeric($this->objPrimary->GetValue()))
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
		$objQuery = Query::Insert($this->strHandle, null,
			$this->GetFieldObject());
		$this->objDB->Query($objQuery);
		$this->objPrimary->SetValue($this->objDB->GetInsertId());
		$this->ResetModified();
		
		$this->Cache();
		return true;
	}
	
	protected function Update()
	{
		$objQuery = Query::Update($this->strHandle, null,
			$this->GetFieldObject())
			->Where($this->objPrimary->getColumnName(), $this->objPrimary->GetValue());
		$this->objDB->Query($objQuery);
		$this->ResetModified();
		$this->Cache();
		return true;
	}
	
	public function LoadByKey($strKey, $strValue)
	{
		//Could GetFromCache use a subclassed pdo object????one!one!
		$this->bolAutoSet = true;
		$objQuery = Query::Select($this->strHandle, NULL, $this->GetFieldObject())
			->Where($strKey, $strValue);
		$this->objDB->Query($objQuery);
		foreach($this->objDB->Fetch() as $strKey => $mxdValue)
		{
			$this->$strKey = $mxdValue;
		}
		
	}
	
	public function IsModified()
	{
		foreach($this->arrColumns as $strColumnName => $objColumn)
		{
			if($objColumn->IsModified() === true)
			{
				return true;
			}
		}
		return false;
	}
	
	protected function GetColumnDefinitions()
	{
		$strKey = __CLASS__.'::ColumnDefinitions.'.$this->strHandle;
		$arrColumns = $GLOBALS['objConfig']->Cache->Get($strKey);
		list($strSchema, $strTable) = explode('.', $this->strHandle);
		if(!$arrColumns)
		{
			$arrColumns = $this->objDB->GetColumnDefinitions($strTable);
			$GLOBALS['objConfig']->Cache->Set($strKey, $arrColumns);
		}
		
		foreach($arrColumns as $objColumn)
		{
			$this->arrColumns[$this->objDB->MapColumnName($objColumn->GetColumnName())] = $objColumn;
			if(!is_null($objColumn->GetJoinSchema()))
			{
				$this->arrObjects[$this->objDB->MapColumnName($objColumn->GetColumnName())] = function($strKey, $mxdValue)
				{
					$objEntity = new Entity($this->arrColumns[$strKey]->GetJoinSchema());
					$objEntity->LoadByKey($this->arrColumns[$strKey]->GetJoinOn(), $mxdValue);
					return $objEntity;
				};
			}
			if($objColumn->IsPrimary())
			{
				$this->objPrimary = $objColumn;
			}
		}
	}
	
	protected function GetFieldObject()
	{
		$objFieldCollection = new Fields();
		foreach($this->arrColumns as $strKey => $objField)
		{
			$strColumnName = $objField->GetColumnName();
			$objFieldCollection->$strColumnName = $objField->GetValue();
		}
		return $objFieldCollection;
	}
	
	protected function ResetModified()
	{
		foreach($this->arrColumns as $objField)
		{
			$objField->SetModified(false);
		}
	}
	
	protected function Cache()
	{
		$arrCache = array();
		foreach($this->arrColumns as $strKey => $objField)
		{
			$arrCache[$strKey] = $objField->GetValue();
		}
	}
}
