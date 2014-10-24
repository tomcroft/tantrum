<?php

namespace tantrum\QueryBuilder;

use tantrum\Exception,
	tantrum\Core;

class Clause extends Core\Module
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
	protected $escaped = true;

	public function setType($type)
	{
		self::validateType($type);
		$this->type = $type;
	}

	public function setEscaped($escaped)
	{
		if(!is_bool($escaped)) {
			throw new Exception\ClauseException('Escape must be a boolean value'); 
		} 
		$this->escaped = $escaped;
	}
	
	/**
	 * Set the arguments for the clause
	 * @param mixed $left  - the left operand
	 * @param mixed $right - the right operand
	 * @param integer $operator - the operator
	 * @throws tantrum\Exception\ClauseException
	 * @return tantrum\QueryBuilder\Clause
	 */
	public function setArgs($left, $right, $operator)
	{
		/**
			TODO: Validate the arguments, if possible
		 */
		$this->validateOperator($operator);
		$this->left = $left;
		$this->right = $right;
		$this->operator = $operator;
		return $this;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getArgs()
	{
		return array($this->left, $this->right);
	}
	
	public function getOperator()
	{
		return $this->operator;
	}
	
	public function isEscaped()
	{
		return $this->escaped;
	}
	
	public static function Where($left, $right, $operator=self::EQUALS, $escaped=false)
	{
		$clause = self::newInstance('tantrum\QueryBuilder\Clause');
		$clause->setType(self::WHERE);
		$clause->setEscaped($escaped);
		$clause->setArgs($left, $right, $operator);
		$clauseCollection = self::newInstance('tantrum\QueryBuilder\ClauseCollection');
		$clauseCollection->addClause($clause);
		$clauseCollection->setType(self::WHERE);
		return $clauseCollection;
	}
	
	public static function On($left, $right, $operator=self::EQUALS, $escaped=false)
	{
		$clause = self::newInstance('tantrum\QueryBuilder\Clause');
		$clause->setType(self::ON);
		$clause->setEscaped($escaped);
		$clause->setArgs($left, $right, $operator);
		$clauseCollection = self::newInstance('tantrum\QueryBuilder\ClauseCollection');
		$clauseCollection->addClause($clause);
		$clauseCollection->setType(self::ON);
		return $clauseCollection;
	}
	
	public static function _And($left, $right, $operator=self::EQUALS, $escaped=true)
	{
		$clause = self::newInstance('tantrum\QueryBuilder\Clause');
		$clause->setType(self::_AND);
		$clause->setEscaped($escaped);
		$clause->setArgs($left, $right, $operator);
		return $clause;
	}
	
	public static function _Or($left, $right, $operator=self::EQUALS, $escaped=true)
	{
		$clause = self::newInstance('tantrum\QueryBuilder\Clause');
		$clause->setType(self::_OR);
		$clause->setEscaped($escaped);
		$clause->setArgs($left, $right, $operator);
		return $clause;
	}

	/**
	 * Ensure a type passed in conforms to the class constants
	 * @param  integer $type
	 * @throws tantrum\Exception\ClauseException
	 * @return boolean
	 */
	public static function validateType($type)
	{
		if(in_array($type, array(
			self::WHERE,
			self::_AND,
			self::_OR,
			self::ON,
		))) {
			return true;
		}
		throw new Exception\ClauseException('Type not handled');		
	}

	protected function validateOperator($operator)
	{
		if(in_array($operator, array(
			self::EQUALS,
			self::NOT_EQUAL,
			self::GREATER_THAN,
			self::LESS_THAN,
		))) {
			return true;
		}
		throw new Exception\ClauseException('Operator not handled');
	}
}
