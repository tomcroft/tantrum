<?php

namespace TomChaton\ClingDB\QueryBuilder;

class Join
{

	const INNER = 1;
	const LEFT = 2;
	const STRAIGHT = 3;
	
	protected $intType;
	protected $strAlias;
	protected $strFrom;
	
	public function __construct($strFrom, $intType = self::STRAIGHT, $objClauseCollection = null)
	{
		//@TODO: Subclass this
		$this->intType = $intType;
		$this->strFrom = $strFrom;
		$this->objClauseCollection = $objClauseCollection;
	}
	
	public function SetAlias($strAlias)
	{
		$this->strAlias = $strAlias;
	}
	
	public function GetAlias()
	{
		return $this->strAlias;
	}
	
	public function GetType()
	{
		return $this->intType;
	}
	
	public function GetTarget()
	{
		return $this->strFrom;
	}
	
	public function GetClauseCollection()
	{
		return $this->objClauseCollection;
	}
	
	public static function Inner($strFrom, ClauseCollection $objClauseCollection)
	{
		$objJoin = new Join($strFrom, self::INNER, $objClauseCollection);
		return $objJoin;
	}
	
	public static function Left($strFrom, ClauseCollection $objClauseCollection)
	{
		$objJoin = new Join($strFrom, self::LEFT, $objClauseCollection);
		return $objJoin;
	}
}
