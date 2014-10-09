<?php

namespace tomcroft\tantrum\QueryBuilder;

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
	
	protected $type;
	protected $operator;
	protected $left;
	protected $right;
	protected $escape;
	
	// public function __construct($type, $escape=true)
	// {
	// 	//@TODO: Subclass this
	// 	$this->type = $type;
	// 	$this->escape = $escape;
	// }
	
	public function SetArgs($left, $right, $operator)
	{
		$this->left = $left;
		$this->right = $right;
		$this->operator = $operator;
		return $this;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function GetArgs()
	{
		return array($this->left, $this->right);
	}
	
	public function GetOperator()
	{
		return $this->operator;
	}
	
	public function Escape()
	{
		return $this->escape;
	}
	
	public static function Where($left, $right, $operator=self::EQUALS, $escape=true)
	{
		$clause = new Clause(self::WHERE, $escape);
		$clause->SetArgs($left, $right, $operator);
		$clauseCollection = new ClauseCollection($clause);
		return $clauseCollection;
	}
	
	public static function On($left, $right, $operator=self::EQUALS, $escape=false)
	{
		$clause = new Clause(self::ON, $bscape);
		$clause->SetArgs($left, $right, $operator);
		$clauseCollection = new ClauseCollection($clause);
		$clauseCollection->setType(self::ON);
		return $clauseCollection;
	}
	
	public static function _And($left, $right, $operator=self::EQUALS, $escape=true)
	{
		$clause = new Clause(self::_AND, $escape);
		return $clause->SetArgs($left, $right, $operator);
	}
	
	public static function _Or($left, $right, $operator=self::EQUALS, $escape=true)
	{
		$clause = new Clause(self::_OR, $escape);
		return $clause->SetArgs($left, $right, $operator);
	}
}
