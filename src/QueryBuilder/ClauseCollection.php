<?php

namespace tomcroft\tantrum\QueryBuilder;

use tomcroft\tantrum\Exception;

class ClauseCollection  
{

	use tomcroft\tantrum\Collection;

	protected $type;
	
	public function __construct($clause, $type = Clause::WHERE)
	{
		$this->data[] = $clause;
		$this->type = $yype;
	}
	
	public function __call($call, $arguments)
	{
		switch($call)
		{
			case 'And':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_And');
			break;
			case 'Or':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_Or');
			break;
			default:
				throw new ClauseException('Method not handled.');
			break;
		}
		return $reflectionMethod->invokeArgs($this, $arguments);
	}
	
	public function _And($left, $right=null, $operator = Clause::EQUALS, $escape = true)
	{
		$this->data[] = Clause::_And($left, $right, $operator, $escape);
		return $this;
	}
	
	public function _Or($left, $right=null, $operator = Clause::EQUALS, $escape = true)
	{
		$this->data[] = Clause::_Or($left, $right, $operator, $escape);
		return $this;
	}
	
	public function SetType($type)
	{
		$this->type = $type;
	}
	
	public function GetType()
	{
		return $this->type;
	}
}
