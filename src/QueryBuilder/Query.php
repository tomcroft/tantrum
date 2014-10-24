<?php

namespace tantrum\QueryBuilder;

use tantrum\Core,
	tantrum\Exception;
	
class Query extends Core\Module
{
	const SELECT = 1;
	const INSERT = 2;
	const UPDATE = 3;
	const DELETE = 4;
	
	const ASC = 100;
	const DESC = 101;
	
	protected $type;
	protected $target;
	protected $alias;
	protected $fields;
	protected $clauses = array();
	protected $joins = array();
	protected $offset;
	protected $limit;
	protected $groupBy = array();
	protected $orderBy = array();
	protected $duplicateFieldsForUpdate;

	/**
	 * Setter for the fields object
	 * @param Fields $fields
	 * @return void
	 */
	public function setFields(Fields $fields)
	{
		$this->fields = $fields;
	}

	/**
	 * Setter for the duplicateFieldsForUpdate object
	 * @param  Fields $fields - an object representing key/value pairs
	 * @return Query
	 */
	public function OnDuplicate(Fields $fields)
	{
		$this->duplicateFieldsForUpdate = $fields;
		return $this;
	}

	/**
	 * Set the table we're acting upon
	 * @param  string $target
	 * @return void
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}

	public function setType($type)
	{
		$this->validateType($type);
		$this->type = $type;
	}

	/**
	 * Setter for the table alias
	 * @param [type] $alias [description]
	 */
	public function setAlias($alias)
	{
		$this->alias = $alias;
	} 

	/**
	 * get the fields this query is inserting / updating
	 * @return Fields
	 */
	public function getFields()
	{
		return $this->fields;
	}
	
	/**
	 * get the fields this query will update on a duplicate key violation
	 */
	public function getDuplicateFieldsForUpdate()
	{
		return $this->duplicateFieldsForUpdate;
	}
	
	/**
	 * Get the table name and schema (dot.notation)
	 */
	public function getTarget()
	{
		return $this->target;
	}
	
	/**
	 * returns an array of joins for this query
	 * @return array
	 */
	public function getJoins()
	{
		return $this->joins;
	}
	
	/**
	 * get the clauses for this query
	 */
	public function getClauses()
	{
		return $this->clauses;
	}
	
	/**
	 * get the offset value
	 * @return integer
	 */
	public function getOffset()
	{
		return $this->offset;
	}
	
	/**
	 * get the limit value
	 * @return integer
	 */
	public function getLimit()
	{
		return $this->limit;
	}
	
	/**
	 * get the alias for the table we're acting upon
	 * @return string
	 */
	public function getAlias()
	{
		//not sure if this should go here..
		return $this->alias;
	}
	
	/**
	 * get the type of query
	 * @return integer
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * get the columns we're grouping by
	 * @return array
	 */
	public function getGroupBy()
	{
		return $this->groupBy;
	}
	
	/**
	 * get the columns we're ordering by
	 * @return array
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}

	/**
	 * Create and return a SELECT query object
	 * @param string $target - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Select($target, $alias = null, $fields = null)
	{
		$query = self::newInstance('tantrum\QueryBuilder\Query');
		$query->setType(self::SELECT);
		$query->setTarget($target);
		$query->setAlias($alias);
		if(!is_null($fields) && ($fields instanceof \tantrum\QueryBuilder\Fields)) {
			$query->setFields($fields);	
		} 
		return $query;
	}
	
	/**
	 * Create and return an INSERT query object
	 * @param string $target - the table we're acting upon
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Insert($target, Fields $fields)
	{
		//TODO: Create an error handler for type hinting so we can throw tantrums ;)
		$query = self::newInstance('tantrum\QueryBuilder\Query');
		$query->setType(self::INSERT);
		$query->setTarget($target);
		$query->setFields($fields);
		return $query;
	}
	
	/**
	 * Create and return a DELETE query object
	 * @param string $target - the table we're acting upon
	 * @param string $alias  - the alias we'll give the table in the query
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Delete($target)
	{
		$query = self::newInstance('tantrum\QueryBuilder\Query');
		$query->setType(self::DELETE);
		$query->setTarget($target);
		return $query;
	}
	
	/**
	 * Create and return an UPDATE query object
	 * @param string $target - the table we're acting upon
	 * @param Fields $fields - an object containing the key/value pairs
	 */
	public static function Update($target, Fields $fields)
	{
		$query = self::newInstance('tantrum\QueryBuilder\Query');
		$query->setType(self::UPDATE);
		$query->setTarget($target);
		$query->setFields($fields);
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
		switch($command) {
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
				throw new Exception\QueryException(sprintf('Method "%s" not handled.', $command));
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
									? $left //TODO: What if this has no clauses inside it?
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
									? $left //TODO: What if this has no clauses inside it?
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
			$this->clauses[] = $left; //TODO: What if this has no clauses inside it?
		} else {
			$this->clauses[] = CLAUSE::_Or($left, $right, $operator, $escape);
		}
		return $this;
	}
	
	/**
	 * Start and limit for SELECT queries
	 * @param  integer  $offset
	 * @param  integer  $limit
	 * @return void
	 */
	public function Limit($offset, $limit = 0)
	{
		if(!is_int($offset)) {
			throw new Exception\QueryException('Offset must be an integer');
		} elseif($offset < 0) {
			throw new Exception\QueryException('Offset must be a positive integer');
		}
		if(!is_int($limit)) {
			throw new Exception\QueryException('Limit must be an integer');
		} elseif($limit < 1) {
			throw new Exception\QueryException('Limit must be a positive integer');
		}
		$this->offset = $offset;
		$this->limit = $limit;
	}
	
	/**
	 * Add an INNER join
	 * @param string           $target           - The schema and table to join to (dot.notation)
	 * @param ClauseCollection $clauseCollection
	 * @param string           $alias            - An alias for the joined table (optional)
	 * @return Query
	 */
	public function InnerJoin($target, ClauseCollection $clauseCollection, $alias=null)
	{
		@list($table, $schema) = explode('.', $target);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$alias = $alias?:uniqid(count($this->joins).$target);
		$join = Join::Inner($target, $clauseCollection);
		$join->setAlias($alias);
		$this->joins[$alias] = $join;
		return $this;
	}
	
	/**
	 * Add a LEFT join
	 * @param  string           $target           - The schema and table to join to (dot.notation)
	 * @param  ClauseCollection $clauseCollection
	 * @param  string           $alias            - An alias for the joined table
	 * @return Query
	 */
	public function LeftJoin($target, ClauseCollection $clauseCollection, $alias=null)
	{
		@list($table, $schema) = explode('.', $target);
		//@TODO: md5 could create an ambiguous or illegal alias name here
		$alias = $alias?:uniqid(count($this->joins).$schema.$table);
		$join = Join::Left($target, $clauseCollection);
		$join->SetAlias($alias);
		$this->joins[$alias] = $join;
		return $this;
	}
	
	/**
	 * Group the results by a field
	 * @param string $groupBy
	 */
	public function GroupBy($groupBy)
	{
		//TODO: If this is attempting to group by an aliased field, validate that alias
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
		$this->validateDirection($direction);
		$this->orderBy[$orderBy] = $direction;
		return $this;
	}
	
	/**
	 * get the values from the key/value pairs inside the clauses (for PDO prepared statement)
	 * @param  array $clauses
	 * @param  array $parameters [description]
	 * @return array
	 */
	public function getParameters($clauses = null, $parameters = array())
	{
		$clauses = is_null($clauses)?$this->clauses:$clauses;
		foreach ($clauses as $clause)	{
			if ($clause instanceof ClauseCollection) {
				$parameters = array_merge($this->GetParameters($clause->ToArray()), $parameters);
			} else {	
				list($left, $right) = $clause->GetArgs();
				if ($clause->isEscaped() === true) {
					$parameters[] = $right;
				}
			}
		}
		return $parameters;
	}

	/**
	 * Validate that the type passed is acceptable
	 * @param  integer $type
	 * @throws QueryException
	 * @return boolean
	 */
	protected function validateType($type)
	{
		if (!in_array($type, array(
			self::SELECT,
			self::INSERT,
			self::UPDATE,
			self::DELETE,
		))) {
			throw new Exception\QueryException('Query type not handled');
		}
		return true;
	}

	protected function validateDirection($direction)
	{
		if (!in_array($direction, array(
			self::ASC,
			self::DESC,
		))) {
			throw new Exception\QueryException('Direction not handled');
		}
		return true;
	}
}
