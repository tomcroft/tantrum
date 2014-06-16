<?php

namespace TomChaton\ClingDB\Database;

class MySQLDB extends DatabaseProvider implements DatabaseAdaptorInterface
{
	protected $strSchema;
	protected $arrNonEscapedStrings = array('NOW()', null);

 	public function __construct($strSchema = 'dbApplications')
	{
		parent::__construct('mysql', $strSchema);
	}
	
	public function FormatSelect(Query $objQuery)
	{
		$strFields = !$objQuery->GetFields()->IsEmpty()?implode(','.PHP_EOL, array_keys($objQuery->GetFields()->ToArray())):'*';
		$strQuery = 'SELECT '.PHP_EOL.$strFields.PHP_EOL.' FROM '.PHP_EOL.$objQuery->GetFrom();
		$strQuery .= $objQuery->GetAlias()?' AS '.$objQuery->GetAlias().PHP_EOL:''.PHP_EOL;

		foreach($objQuery->GetJoins() as $objJoin)
		{
			$strQuery .= $this->FormatJoin($objJoin);
		}
		$arrClauses = $objQuery->GetClauses();

		if(count($arrClauses) > 0)
		{
			$strQuery .= ' WHERE ';
			
			foreach($arrClauses as $mxdClause)
			{
				if($mxdClause instanceof Clause)
				{
					$strQuery .= $this->FormatClause($mxdClause);
				}
				elseif($mxdClause instanceof ClauseCollection)
				{
					$strQuery .= $this->FormatClauseCollection($mxdClause);
				}
			}
		}
		$strQuery .= $this->FormatGroupBy($objQuery->GetGroupBy());
		$strQuery .= $this->FormatOrderBy($objQuery->GetOrderBy());
		$strQuery .= $this->FormatLimit($objQuery->GetStart(), $objQuery->GetOffset());
		//_el($strQuery);
		return $strQuery;
	}
    
  public function FormatInsert(Query $objQuery)
  {
  	$arrPlaceholders = array_fill(0, count($objQuery->GetFields()->ToArray()), '?');
  	$strQuery = 'INSERT INTO '.$objQuery->GetFrom().
  		' ('.implode(',',array_keys($objQuery->GetFields()->ToArray())).')'.
  		' VALUES '.
  		' ('.implode(',', $arrPlaceholders).')';
  	if(!is_null($objQuery->GetDuplicateFieldsForUpdate()))
  	{
  		$strQuery .= ' ON DUPLICATE KEY UPDATE ';
  		$arrFields = array();
  		foreach(array_keys($objQuery->GetDuplicateFieldsForUpdate()->ToArray()) as $strKey)
  		{
  			$arrFields[] = $strKey.' = ?';
			}
			$strQuery .= implode(',',$arrFields);
  	}
  	return $strQuery;
  }
  
  public function FormatDelete(Query $objQuery)
  {
  	$strQuery = 'DELETE FROM '.$objQuery->GetFrom();
  	$strQuery .= $objQuery->GetAlias()?' AS '.$objQuery->GetAlias().PHP_EOL:''.PHP_EOL;
  	foreach($objQuery->GetJoins() as $objJoin)
		{
			$strQuery .= $this->FormatJoin($objJoin);
		}
		$strQuery .= ' WHERE ';
  	foreach($objQuery->GetClauses() as $mxdClause)
  	{
  		if($mxdClause instanceof Clause)
  		{
  			$strQuery .= $this->FormatClause($mxdClause);
  		}
  		elseif($mxdClause instanceof ClauseCollection)
  		{
  			$strQuery .= $this->FormatClauseCollection($mxdClause);
  		}
  	}
  	$strQuery .= $this->FormatGroupBy($objQuery->GetGroupBy());
	$strQuery .= $this->FormatOrderBy($objQuery->GetOrderBy());
	$strQuery .= $this->FormatLimit($objQuery->GetStart(), $objQuery->GetOffset()); 
  	return $strQuery;
  }
   
  public function FormatUpdate(Query $objQuery)
  {
  	$strQuery = 'UPDATE '.$objQuery->GetFrom();
  	
  	$strQuery .= $objQuery->GetAlias()?' AS '.$objQuery->GetAlias().' SET '.PHP_EOL:' SET '.PHP_EOL;
  	
  	$strQuery .= implode(' = ?, ', array_keys($objQuery->GetFields()->ToArray())).' = ?';
  	
  	$strQuery .= ' WHERE ';
  	foreach($objQuery->GetClauses() as $mxdClause)
  	{
  		if($mxdClause instanceof Clause)
  		{
  			$strQuery .= $this->FormatClause($mxdClause);
  		}
  		elseif($mxdClause instanceof ClauseCollection)
  		{
  			$strQuery .= $this->FormatClauseCollection($mxdClause);
  		}
  	}
  	return $strQuery;
  }
	
	public function GetColumnDefinitions($strTable)
	{
		$objQuery = Query::Select('information_schema.COLUMNS','c',
			new Fields('c.COLUMN_NAME AS strColumnName',
				'c.DATA_TYPE AS strDataType',
				'IF(c.IS_NULLABLE="No",1,0) AS bolRequired',
				'c.CHARACTER_MAXIMUM_LENGTH AS intMaximumLength',
				'c.COLUMN_KEY AS strColumnKey',
				'kcu.REFERENCED_TABLE_SCHEMA as strJoinDatabase',
				'kcu.REFERENCED_TABLE_NAME as strJoinTable',
				'kcu.REFERENCED_COLUMN_NAME as strJoinOn',				
				'0 AS bolExtensionColumn',
				'0 as bolModified',
				'IF(kcu2.COLUMN_NAME IS NOT NULL, 1, 0) AS bolHasExternalReferences',
				'c.ORDINAL_POSITION AS intOrdinalPosition',
				'kcu.POSITION_IN_UNIQUE_CONSTRAINT AS intPositionInUniqueConstraint'))
			->LeftJoin('information_schema.KEY_COLUMN_USAGE', Clause::On('kcu.COLUMN_NAME','c.COLUMN_NAME'), 'kcu')
			->LeftJoin('information_schema.KEY_COLUMN_USAGE', Clause::On('kcu2.TABLE_SCHEMA','c.TABLE_NAME')->And('kcu2.COLUMN_NAME', 'c.COLUMN_NAME', Clause::EQUALS, false), 'kcu2')
			->Where('c.TABLE_SCHEMA', $this->strSchema)
			->And('c.TABLE_NAME', $strTable)
			->GroupBy('concat(c.COLUMN_NAME, c.TABLE_NAME, c.TABLE_SCHEMA)')
			->OrderBy('c.ORDINAL_POSITION');

		$this->Query($objQuery);
		$arrFields = $this->FetchAll('priism\Classes\Database\Field');

		/*foreach($arrDBColumns as $arrColumnDefinition)
		{
			$arrColumns[self::MapColumnName($arrColumnDefinition['strColumnName'])] = array (
				'bolRequired' => $arrColumnDefinition['strColumnKey']!='PRI'?$arrColumnDefinition['bolRequired']:NULL,
				'strDataType' => $arrColumnDefinition['strDataType'],
				'intMaximumLength' => $arrColumnDefinition['intMaximumLength'],
				'strColumnKey' => $arrColumnDefinition['strColumnKey'],
				'strJoinDatabase' => $arrColumnDefinition['strJoinDatabase'],
				'strJoinTable' => $arrColumnDefinition['strJoinTable'],
				'strJoinOn' => $arrColumnDefinition['strJoinOn'],
				'bolExtentionColumn' => $arrColumnDefinition['bolExtensionColumn']
			);
			
			if($arrColumnDefinition['bolHasExternalReferences'] == 1)
			{
				// Remind me again, what are external references for???
				// maybe they were for determining whether a column was editable...
				// maybe to work out if this is a link / lookup table.
				$arrExternalReferences = $this->GetExternalReferences($this->strSchema, $strTable, $arrColumnDefinition['strColumnName']);
			}
		} */
		//TODO: Determine the relationship
		return $arrFields;
	}
	
	protected function GetExternalReferences($strDatabase, $strTable, $strColumn)
	{
		$strQuery = '
			SELECT
				REFERENCED_COLUMN_NAME AS strColumnName,
				REFERENCED_TABLE_NAME AS strTableName,
				REFERENCED_TABLE_SCHEMA as strDatabaseName
			FROM
				information_schema.KEY_COLUMN_USAGE
			WHERE
				TABLE_SCHEMA = '.$this->Escape($strDatabase).'
			AND
				TABLE_NAME = '.$this->Escape($strTable).'
			AND
				COLUMN_NAME = '.$this->Escape($strColumn);

		$this->Query($strQuery);
		return $this->FetchAll();
	}
	
	protected function FormatJoin(Join $objJoin)
	{
		switch($objJoin->GetType())
		{
			case Join::INNER:
				$strJoinType = 'INNER';
			break;
			case Join::LEFT:
				$strJoinType = 'LEFT';
			break;
			default:
				throw new Exception('Join type not handled');
			break;
		}
		return sprintf(' %s JOIN %s AS %s %s', $strJoinType, $objJoin->GetTarget(), $objJoin->GetAlias(), $this->FormatClauseCollection($objJoin->GetClauseCollection())).PHP_EOL;
	}
	
	protected function FormatClause(Clause $objClause, $strClause = '')
	{
		list($mxdLeft, $mxdRight) = $objClause->getArgs();
		
		$strClause .= $this->FormatOperator($objClause->getType());
		$strClause .= $mxdLeft;
		$strClause .= $this->FormatOperator($objClause->getOperator());
		$strClause .= $objClause->Escape()?'?':$mxdRight;
	
		return $strClause."\r\n";
	}
	
	protected function FormatClauseCollection(ClauseCollection $objClauseCollection)
	{
		switch($objClauseCollection->Count())
		{
			case 0: 
				return '';
				break;
			case 1:
				return $this->FormatClause($objClauseCollection->ToArray()[0]);
				break;
			default:
				$strReturn = ($objClauseCollection->GetType()==Clause::ON)?'':$this->FormatOperator($objClauseCollection->GetType()).'(';
				foreach($objClauseCollection->ToArray() as $objClause)
				{
					$strReturn = $this->FormatClause($objClause, $strReturn);
				}
				$strReturn .= ($objClauseCollection->GetType()==Clause::ON)?'':')';
				return $strReturn;
				break;
		}
	}
	
	protected function FormatOperator($intOperator)
	{
		switch($intOperator)
		{
			case Clause::WHERE:
				return '';
				break;
			case Clause::EQUALS:
				return ' = ';
				break;
			case Clause::NOT_EQUAL:
				return ' <=> ';
				break;
			case Clause::LESS_THAN:
				return ' < ';
				break;
			case Clause::GREATER_THAN:
				return ' > ';
				break;
			case Clause::_AND:
				return ' AND ';
				break;
			case Clause::_OR:
				return ' OR ';
				break;
			case Clause::ON:
				return ' ON ';
				break;
			default:
				throw new Exception('Operator not handled');
				break;
		}
	}
	
	protected function FormatGroupBy($arrGroupBy)
	{
		if(count($arrGroupBy) > 0)
		{
			return ' GROUP BY '.implode("\r\n",$arrGroupBy);
		}
	}
	
	protected function FormatOrderBy($arrOrderBy)
	{
		if(count($arrOrderBy) == 0)
		{
			return;
		}
		$strOrderBy = PHP_EOL.' ORDER BY ';
		foreach($arrOrderBy as $strField => $intDirection)
		{
			$strOrderBy .= $strField;
			switch($intDirection)
			{
			 	case Query::ASC:
			 		$strOrderBy .= ' ASC';
			 		break;
			 	case Query::DESC:
			 		$strOrderBy .= ' DESC';
			 		break;
			 	default:
			 		throw new Exception('Order by direction not handled');
			 		break;
			}
		}
		return $strOrderBy;
	}
	
	protected function FormatLimit($intNumber, $intOffset)
	{
		if(is_null($intNumber) && is_null($intOffset))
		{
			return;
		}
		elseif($intNumber > 0 && is_null($intOffset))		
		{
			return sprintf(' LIMIT %u', $intNumber);
		}
		else
		{
			return sprintf(' LIMIT %u,%u ', $intNumber, $intOffset);
		}
	}
	
	protected function FormatFields($objFields)
	{
		$strReturn = '';

		foreach($objFields->ToArray() as $sKey => $mxdValue)
		{
			$strReturn .= ' '.$sKey.' = ?';
		}
		return $strReturn;
	}
}
