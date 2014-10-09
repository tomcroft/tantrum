<?php

namespace tomcroft\tantrum\QueryBuilder;
	
class Query 
{
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;
	
	const ASC = 100;
	const DESC = 101;
	
	protected $type;
	protected $name;
	protected $alias;
	protected $fields;
	protected $clauses = array();
	protected $joins = array();
	protected $offset;
	protected $start;
	protected $groupBy = array();
	protected $orderBy = array();
	protected $duplicateFieldsForUpdate;
	
	/**
	 * @param integer $type  - one of self::SELECT, self::INSERT, self::UPDATE or self::DELETE 
	 * @param string $name   - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public function __construct($type, $name, $alias=null, $fields=null)
	{
		$this->type   = $type;
		$this->fields = $fields?:new Fields();
		$this->table  = $name;
		$this->alias  = $alias;
	}
	
	/**
	 * Create and return a SELECT query object
	 * @param string $name   - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Select($name, $alias=null, Fields $fields=null)
	{
		$query = new Query(self::SELECT, $name, $alias, $fields);
		return $query;
	}
	
	/**
	 * Create and return an INSERT query object
	 * @param string $name   - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Insert($name, $alias=null, Fields $fields)
	{
		$query = new Query(self::INSERT, $name, $alias, $fields);
		return $query;
	}
	
	/**
	 * Create and return a DELETE query object
	 * @param string $name   - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Delete($name, $alias=null)
	{
		$query = new Query(self::DELETE, $name, $alias);
		return $query;
	}
	
	/**
	 * Create and return an UPDATE query object
	 * @param string $name   - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Update($name, $alias=null, Fields $fields)
	{
		$query = new Query(self::UPDATE, $name, $alias, $fields);
		return $query;
	}
	
	/**
	 * Used to automatically build 
	 * @param  string $command
	 * @param  array $arguments
	 * @return mixed
	 */
	public function __call($command, $arguments)
	{
		switch($command)
		{
			case 'And':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_And');
			break;
			case 'Where':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_Where');
			break;
			case 'Or':
				$reflectionMethod = new \ReflectionMethod(__CLASS__, '_Or');
			break;
			default:
				throw new QueryException(sprintf('Method "%s" not handled.', $command));
			break;
		}
		
		return $reflectionMethod->invokeArgs($this, $arguments);
	}
	
	/**
	 * Add a "WHERE" clause
	 * @param  mixed   $left      - a Clause or ClauseCollection
	 * @param  mixed   $right     - a Clause or ClauseCollection
	 * @param  integer $operator  - Clause class constant '=', '!=', '<' or '>'
	 * @param  boolean $escape    - whether the clause arguments should be escaped
	 * @return Query
	 */
	public function _Where($left, $right=null, $operator = Clause::EQUALS, $escape=true)
	{
		$this->clauses[] = ($left instanceof ClauseCollection)
									? $left
									: Clause::Where($left, $right, $operator, $escape);
		return $this;
	}
	
	/**
	 * Add an "AND" clause
	 * @param  mixed   $left      - a Clause or ClauseCollection
	 * @param  mixed   $right     - a Clause or ClauseCollection
	 * @param  integer $operator  - Clause class constant '=', '!=', '<' or '>'
	 * @param  boolean $escape    - whether the clause arguments should be escaped
	 * @return Query
	 */
	public function _And($left, $right=null, $operator = Clause::EQUALS, $escape=true)
	{
		$this->clauses[] = ($left instanceof ClauseCollection)
									? $left
									: CLAUSE::_And($left, $right, $operator, $escape);
		return $this;
	}
	
	/**
	 * Add an "OR" clause
	 * @param  mixed   $left      - a Clause or ClauseCollection
	 * @param  mixed   $right     - a Clause or ClauseCollection
	 * @param  integer $operator  - Clause class constant '=', '!=', '<' or '>'
	 * @param  boolean $escape    - whether the clause arguments should be escaped
	 * @return Query
	 */
	public function _Or($left, $right=null, $operator = Clause::EQUALS, $escape=true)
	{
		if ($left instanceof ClauseCollection) {
			$left->SetType(Clause::_OR);
			$this->clauses[] = $left;
		} else {
			CLAUSE::_Or($left, $right, $operator, $escape);
		}
		return $this;
	}
	
	/**
	 * Start and limit for SELECT queries
	 * @param  integer  $offset
	 * @param  integer  $start
	 * @return void
	 */
	public function Limit($offset, $start = 0)
	{
		$this->offset = $offset;
		$this->start = $start;
	}
	
	/**
	 * Add an INNER join
	 * @param string           $name             - The schema and table to join to (dot.notation)
	 * @param ClauseCollection $clauseCollection
	 * @param string           $alias            - An alias for the joined table
	 * @return Query
	 */
	public function InnerJoin($name, ClauseCollection $objClauseCollection, $alias=null)
	{
		list($table, $schema) = explode('.', $name);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$alias = $alias?:uniqid(count($this->joins).$name);
		$join = Join::Inner($name, $clauseCollection);
		$join->SetAlias($alias);
		$this->joins[$alias] = $join;
		return $this;
	}
	
	/**
	 * Add a LEFT join
	 * @param  string           $name             - The schema and table to join to (dot.notation)
	 * @param  ClauseCollection $clauseCollection
	 * @param  string           $alias            - An alias for the joined table
	 * @return Query
	 */
	public function LeftJoin($name, ClauseCollection $clauseClollection, $alias=null)
	{
		list($table, $schema) = explode('.', $name);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$alias = $alias?:uniqid(count($this->joins).$strSchema.$strTable);
		$join = Join::Left($name, $clauseCollection);
		$join->SetAlias($alias);
		$this->joins[$alias] = $join;
		return $this;
	}
	
	/**
	 * Group the results by a field
	 * @param [type] $groupBy [description]
	 */
	public function GroupBy($groupBy)
	{
		$this->groupBy[] = $groupBy;
		return $this;
	}
	
	/**
	 * Order the results by a field
	 * @param string  $orderBy   - the field by which to order
	 * @param integer $direction - ASC or DESC
	 */
	public function OrderBy($orderBy, $direction=self::ASC)
	{
		$this->orderBy[$orderBy] = $direction;
		return $this;
	}
	
	/**
	 * List fields to update on a duplicate key violation
	 * @param  Fields $fields - an object representing key/value pairs
	 * @return Query
	 */
	public function OnDuplicate(Fields $fields)
	{
		$this->duplicateFieldsForUpdate = $fields;
		return $this;
	}
	
	/**
	 * get the fields this query is inserting / updating
	 * @return Fields
	 */
	public function GetFields()
	{
		return $this->fields;
	}
	
	/**
	 * get the fields this query will update on a duplicate key violation
	 */
	public function GetDuplicateFieldsForUpdate()
	{
		return $this->duplicateFieldsForUpdate;
	}
	
	/**
	 * get the table name and schema (dot.notation)
	 */
	public function GetFrom()
	{
		return $this->from;
	}
	
	/**
	 * returns an array of joins for this query
	 * @return array
	 */
	public function GetJoins()
	{
		return $this->joins;
	}
	
	/**
	 * get the clauses for this query
	 */
	public function GetClauses()
	{
		return $this->clauses;
	}
	
	/**
	 * get the offset value
	 * @return integer
	 */
	public function GetOffset()
	{
		return $this->offset;
	}
	
	/**
	 * get the start value
	 * @return integer
	 */
	public function GetStart()
	{
		return $this->start;
	}
	
	/**
	 * get the alias for the table we're acting upon
	 * @return string
	 */
	public function GetAlias()
	{
		//not sure if this should go here..
		return $this->alias;
	}
	
	/**
	 * get the type of query
	 * @return integer
	 */
	public function GetType()
	{
		return $this->type;
	}
	
	/**
	 * get the columns we're grouping by
	 * @return array
	 */
	public function GetGroupBy()
	{
		return $this->groupBy;
	}
	
	/**
	 * get the columns we're ordering by
	 * @return array
	 */
	public function GetOrderBy()
	{
		return $this->orderBy;
	}
	
	/**
	 * get the values from the key/value pairs inside the clauses (for PDO prepared statement)
	 * @param  array $clauses
	 * @param  array $parameters [description]
	 * @return array
	 */
	public function GetParameters($clauses = null, $parameters = array())
	{
		$clauses = is_null($clauses)?$this->clauses:$clauses;
		foreach ($clauses as $clause)	{
			if ($clause instanceof ClauseCollection) {
				$parameters = array_merge($this->GetParameters($clause->ToArray()), $parameters);
			} else {	
				list($left, $right) = $clause->GetArgs();
				if ($clause->Escape() === true) {
					$parameters[] = $right;
				}
			}
		}
		return $parameters;
	}
}
