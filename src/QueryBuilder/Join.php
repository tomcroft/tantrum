<?php

namespace tantrum\QueryBuilder;

use tantrum\Core,
	tantrum\Exception;

class Join extends Core\Module
{

	const INNER = 1;
	const LEFT = 2;
	
	protected $type;
	protected $alias;
	protected $target;
	
	public function setAlias($alias)
	{
		//TODO: Validate this?
		$this->alias = $alias;
	}

	public function setType($type)
	{
		$this->validateType($type);
		$this->type = $type;
	}

	public function setTarget($target)
	{
		//TODO: Validate this? 
		$this->target = $target;
	}

	public function setClauseCollection(ClauseCollection $clauseCollection)
	{
		$this->clauseCollection = $clauseCollection;
	}
	
	public function getAlias()
	{
		return $this->alias;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function getTarget()
	{
		return $this->target;
	}
	
	public function getClauseCollection()
	{
		return $this->clauseCollection;
	}
	
	public static function Inner($target, ClauseCollection $clauseCollection)
	{
		$join = self::newInstance('tantrum\QueryBuilder\Join');
		$join->setTarget($target);
		$join->setType(self::INNER);
		$join->setClauseCollection($clauseCollection);
		return $join;
	}
	
	public static function Left($target, ClauseCollection $clauseCollection)
	{
		$join = self::newInstance('tantrum\QueryBuilder\Join');
		$join->setTarget($target);
		$join->setType(self::LEFT);
		$join->setClauseCollection($clauseCollection);
		return $join;
	}

	protected function validateType($type)
	{
		if (!in_array($type, array(
			self::INNER,
			self::LEFT,
		))) {
			throw new Exception\JoinException('Join type not handled');
		}
		return true;
	}
}
