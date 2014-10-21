<?php

namespace tomcroft\tantrum\QueryBuilder;

use tomcroft\tantrum\Exception,
	tomcroft\tantrum\Core;

class ClauseCollection extends Core\Module
{

	use \tomcroft\tantrum\Traits\Collection;

	protected $type;
	
	public function __call($call, $arguments)
	{
		switch($call) {
			case 'And':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_And');
			break;
			case 'Or':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_Or');
			break;
			default:
				throw new Exception\ClauseException('Method "'.$call.'" not handled.');
			break;
		}
		return $reflectionMethod->invokeArgs($this, $arguments);
	}
	
	public function _And($left, $right=null, $operator = Clause::EQUALS, $escaped = true)
	{
		$this->data[] = Clause::_And($left, $right, $operator, $escaped);
		return $this;
	}
	
	public function _Or($left, $right=null, $operator = Clause::EQUALS, $escaped = true)
	{
		$this->data[] = Clause::_Or($left, $right, $operator, $escaped);
		return $this;
	}

	public function addClause(Clause $clause)
	{
		$this->data[] = $clause;
	}
	
	public function setType($type)
	{
		Clause::validateType($type);
		$this->type = $type;
	}
	
	public function getType()
	{
		return $this->type;
	}
}
