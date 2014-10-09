<?php

namespace tomcroft\tantrum\QueryBuilder;

class Join
{

	const INNER = 1;
	const LEFT = 2;
	const STRAIGHT = 3;
	
	protected $type;
	protected $alias;
	protected $from;
	
	public function __construct($from, $type = self::STRAIGHT, $clauseCollection = null)
	{
		//@TODO: Subclass this
		$this->type = $type;
		$this->from = $from;
		$this->clauseCollection = $clauseCollection;
	}
	
	public function SetAlias($alias)
	{
		$this->alias = $alias;
	}
	
	public function GetAlias()
	{
		return $this->slias;
	}
	
	public function GetType()
	{
		return $this->type;
	}
	
	public function GetTarget()
	{
		return $this->from;
	}
	
	public function GetClauseCollection()
	{
		return $this->clauseCollection;
	}
	
	public static function Inner($from, ClauseCollection $clauseCollection)
	{
		$join = new Join($from, self::INNER, $clauseCollection);
		return $join;
	}
	
	public static function Left($from, ClauseCollection $clauseCollection)
	{
		$join = new Join($from, self::LEFT, $clauseCollection);
		return $join;
	}
}
