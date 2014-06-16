<?php

namespace TomChaton\ClingDB\QueryBuilder;

class Clause
{
	const WHERE = 0;
	const _AND = 1;
	const _OR = 2;
	const ON = 3;
	
	const EQUALS = 100;
	const NOT_EQUAL = 101;
	const LESS_THAN = 102;
	const GREATER_THAN = 103;
	
	protected $intType;
	protected $intOperator;
	protected $mxdLeft;
	protected $mxdRight;
	protected $bolEscape;
	
	public function __construct($intType, $bolEscape=true)
	{
		//@TODO: Subclass this
		$this->intType = $intType;
		$this->bolEscape = $bolEscape;
	}
	
	public function SetArgs($mxdLeft, $mxdRight, $intOperator)
	{
		$this->mxdLeft = $mxdLeft;
		$this->mxdRight = $mxdRight;
		$this->intOperator = $intOperator;
		return $this;
	}
	
	public function GetType()
	{
		return $this->intType;
	}
	
	public function GetArgs()
	{
		return array($this->mxdLeft, $this->mxdRight);
	}
	
	public function GetOperator()
	{
		return $this->intOperator;
	}
	
	public function Escape()
	{
		return $this->bolEscape;
	}
	
	public static function Where($mxdLeft, $mxdRight, $intOperator=self::EQUALS, $bolEscape=true)
	{
		$objClause = new Clause(self::WHERE, $bolEscape);
		$objClause->SetArgs($mxdLeft, $mxdRight, $intOperator);
		$objClauseCollection = new ClauseCollection($objClause);
		return $objClauseCollection;
	}
	
	public static function On($mxdLeft, $mxdRight, $intOperator=self::EQUALS, $bolEscape=false)
	{
		$objClause = new Clause(self::ON, $bolEscape);
		$objClause->SetArgs($mxdLeft, $mxdRight, $intOperator);
		$objClauseCollection = new ClauseCollection($objClause);
		$objClauseCollection->setType(self::ON);
		return $objClauseCollection;
	}
	
	public static function _And($mxdLeft, $mxdRight, $intOperator=self::EQUALS, $bolEscape=true)
	{
		$objClause = new Clause(self::_AND, $bolEscape);
		return $objClause->SetArgs($mxdLeft, $mxdRight, $intOperator);
	}
	
	public static function _Or($mxdLeft, $mxdRight, $intOperator=self::EQUALS, $bolEscape=true)
	{
		$objClause = new Clause(self::_OR, $bolEscape);
		return $objClause->SetArgs($mxdLeft, $mxdRight, $intOperator);
	}
}
