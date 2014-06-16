<?php

namespace TomChaton\ClingDB\QueryBuilder;

use TomChaton\ClingDB\Exception;

class ClauseCollection  
{

	use TomChaton\clingDB\Collection;

	protected $intType;
	
	public function __construct($objClause, $intType = Clause::WHERE)
	{
		$this->arrData[] = $objClause;
		$this->intType = $intType;
	}
	
	public function __call($strCall, $arrArguments)
	{
		switch($strCall)
		{
			case 'And':
				$objReflectionMethod = new \ReflectionMethod(__CLASS__, '_And');
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
	
	public function _And($mxdLeft, $mxdRight=null, $intOperator = Clause::EQUALS, $bolEscape = true)
	{
		$this->arrData[] = Clause::_And($mxdLeft, $mxdRight, $intOperator, $bolEscape);
		return $this;
	}
	
	public function _Or($mxdLeft, $mxdRight=null, $intOperator = Clause::EQUALS, $bolEscape = true)
	{
		$this->arrData[] = Clause::_Or($mxdLeft, $mxdRight, $intOperator, $bolEscape);
		return $this;
	}
	
	public function SetType($intType)
	{
		$this->intType = $intType;
	}
	
	public function GetType()
	{
		return $this->intType;
	}
}
