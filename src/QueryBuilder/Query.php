<?php

namespace TomChaton\clingDB\QueryBuilder;
	
class Query 
{
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;
	
	const ASC = 100;
	const DESC = 101;
	
	protected $intType;
	protected $strFrom;
	protected $strAlias;
	protected $objFields;
	protected $arrClauses = array();
	protected $arrJoins = array();
	protected $intOffset;
	protected $intStart;
	protected $arrGroupBy = array();
	protected $arrOrderBy = array();
	protected $objDuplicateFieldsForUpdate;
	
	public function __construct($intType, $strFrom, $strAlias=null, $objFields=null)
	{
		$this->intType = $intType;
		$this->objFields = $objFields?:new Fields();
		$this->strFrom = $strFrom;
		$this->strAlias = $strAlias;
	}
	
	public static function Select($strFrom, $strAlias=null, Fields $objFields=null)
	{
		$objQuery = new Query(self::SELECT, $strFrom, $strAlias, $objFields);
		return $objQuery;
	}
	
	public static function Insert($strFrom, $strAlias=null, Fields $objFields)
	{
		$objQuery = new Query(self::INSERT, $strFrom, $strAlias, $objFields);
		return $objQuery;
	}
	
	public static function Delete($strFrom, $strAlias=null)
	{
		$objQuery = new Query(self::DELETE, $strFrom, $strAlias);
		return $objQuery;
	}
	
	public static function Update($strFrom, $strAlias=null, Fields $objFields)
	{
		$objQuery = new Query(self::UPDATE, $strFrom, $strAlias, $objFields);
		return $objQuery;
	}
	
	public function __call($strCommand, $arrArguments)
	{
		switch($strCommand)
		{
			case 'And':
				$objReflectionMethod = new \ReflectionMethod(__CLASS__, '_And');
			break;
			case 'Where':
				$objReflectionMethod = new \ReflectionMethod(__CLASS__, '_Where');
			break;
			case 'Or':
				$objReflectionMethod = new \ReflectionMethod(__CLASS__, '_Or');
			break;
			default:
				throw new Exception('Method not handled.');
			break;
		}
		
		return $objReflectionMethod->invokeArgs($this, $arrArguments);
	}
	
	public function _Where($mxdLeft, $mxdRight=null, $intOperator = Clause::EQUALS, $bolEscape=true)
	{
		$this->arrClauses[] = ($mxdLeft instanceof ClauseCollection)
														? $mxdLeft
														: Clause::Where($mxdLeft, $mxdRight, $intOperator, $bolEscape);
		return $this;
	}
	
	public function _And($mxdLeft, $mxdRight=null, $intOperator = Clause::EQUALS, $bolEscape=true)
	{
		$this->arrClauses[] = ($mxdLeft instanceof ClauseCollection)
														? $mxdLeft
														: CLAUSE::_And($mxdLeft, $mxdRight, $intOperator, $bolEscape);
		return $this;
	}
	
	public function _Or($mxdLeft, $mxdRight=null, $intOperator = Clause::EQUALS, $bolEscape=true)
	{
		if($mxdLeft instanceof ClauseCollection)
		{
			$mxdLeft->SetType(Clause::_OR);
			$this->arrClauses[] = $mxdLeft;
		}
		else
		{
			CLAUSE::_Or($mxdLeft, $mxdRight, $intOperator, $bolEscape);
		}
		return $this;
	}
	
	public function Limit($intOffset, $intStart = 0)
	{
		$this->intOffset = $intOffset;
		$this->intStart = $intStart;
	}
	
	public function InnerJoin($strFrom, ClauseCollection $objClauseCollection, $strAlias=null)
	{
		list($strTable, $strSchema) = explode('.', $strFrom);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$strAlias = $strAlias?:md5(count($this->arrJoins).$strFrom);
		$objJoin = Join::Inner($strFrom, $objClauseCollection);
		$objJoin->SetAlias($strAlias);
		$this->arrJoins[$strAlias] = $objJoin;
		return $this;
	}
	
	public function LeftJoin($strFrom, ClauseCollection $objClause, $strAlias=null)
	{
		list($strTable, $strSchema) = explode('.', $strFrom);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$strAlias = $strAlias?:md5(count($this->arrJoins).$strSchema.$strTable);
		$objJoin = Join::Left($strFrom, $objClause);
		$objJoin->SetAlias($strAlias);
		$this->arrJoins[$strAlias] = $objJoin;
		return $this;
	}
	
	public function GroupBy($strGroupBy)
	{
		$this->arrGroupBy[] = $strGroupBy;
		return $this;
	}
	
	public function OrderBy($strOrderBy, $intDirection=self::ASC)
	{
		$this->arrOrderBy[$strOrderBy] = $intDirection;
		return $this;
	}
	
	public function OnDuplicate(Fields $objFields)
	{
		$this->objDuplicateFieldsForUpdate = $objFields;
		return $this;
	}
	
	public function GetFields()
	{
		return $this->objFields;
	}
	
	public function GetDuplicateFieldsForUpdate()
	{
		return $this->objDuplicateFieldsForUpdate;
	}
	
	public function GetFrom()
	{
		return $this->strFrom;
	}
	
	public function GetJoins()
	{
		return $this->arrJoins;
	}
	
	public function GetClauses()
	{
		return $this->arrClauses;
	}
	
	public function GetOffset()
	{
		return $this->intOffset;
	}
	
	public function GetStart()
	{
		return $this->intStart;
	}
	
	public function GetAlias()
	{
		//not sure if this should go here..
		return $this->strAlias;
	}
	
	public function GetType()
	{
		return $this->intType;
	}
	
	public function GetGroupBy()
	{
		return $this->arrGroupBy;
	}
	
	public function GetOrderBy()
	{
		return $this->arrOrderBy;
	}
	
	public function GetParameters($arrClauses = NULL, $arrParameters = array())
	{
		$arrClauses = is_null($arrClauses)?$this->arrClauses:$arrClauses;
		foreach($arrClauses as $mxdClause)
		{
			if($mxdClause instanceof ClauseCollection)
			{
				$arrParameters = array_merge($this->GetParameters($mxdClause->ToArray()), $arrParameters);
			}
			else
			{	
				list($mxdLeft, $mxdRight) = $mxdClause->GetArgs();
				if($mxdClause->Escape() === true)
				{
						$arrParameters[] = $mxdRight;
				}
			}
		}
		return $arrParameters;
	}
}
