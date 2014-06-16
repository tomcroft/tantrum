<?php

namespace TomChaton\clingDB\Database;

use TomChaton\clingDB\Exception;

class DatabaseProvider
{
	const KEY_PRIMARY = 'PRI';
	const KEY_FOREIGN = 'FOREIGN';
	const KEY_UNIQUE = 'UNIQUE';
	
	const REL_TYPE_ONE_TO_ONE = '121';
	const REL_TYPE_ONE_TO_MANY = '12n';
	const REL_TYPE_MANY_TO_MANY = 'n2n';
	
	const TYPE_STRING = 'string';
	const TYPE_INTEGER = 'integer';
	
	protected $strSchema;
	protected $resConnection;
	protected $objStatement;
	protected $strPrimaryKey;
	protected $arrFields = array();
	protected $arrJoins = array();
	protected $arrData = array();

	public function __construct($strDriver, $strSchema)
	{
		$this->strSchema = $strSchema;
		$strDataSourceName = $strDriver.':host='.$GLOBALS['objConfig']->arrDatabase['strHost'].';dbname='.$strSchema;
		$arrOptions = array(
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
		); 
		$this->resConnection = new \PDO($strDataSourceName, $GLOBALS['objConfig']->arrDatabase['strUserName'], $GLOBALS['objConfig']->arrDatabase['strPassword'], $arrOptions);
	}
	
	public static function Get($strSchema)
	{
		$strClassName = __NAMESPACE__.'\\'.$GLOBALS['objConfig']->arrDatabase['strDriver'].'DB';
		return new $strClassName($strSchema);
	}
	
	public function Query(Query $objQuery)
	{
		switch($objQuery->getType())
		{
			case Query::SELECT: 
				$strQuery = $this->FormatSelect($objQuery);
				$arrParameters = $objQuery->GetParameters();
				break;
			case Query::INSERT:
				$strQuery = $this->FormatInsert($objQuery);
				$arrParameters = array_values($objQuery->getFields()->ToArray());
				$arrParameters = !is_null($objQuery->GetDuplicateFieldsForUpdate())
					? array_merge($arrParameters, array_values($objQuery->GetDuplicateFieldsForUpdate()->ToArray()))
					: $arrParameters;
				break;
			case Query::DELETE:
				$strQuery = $this->FormatDelete($objQuery);
				$arrParameters = $objQuery->GetParameters();
				break;
			case Query::UPDATE:
				$strQuery = $this->FormatUpdate($objQuery);
				$arrParameters = array_merge(array_values($objQuery->getFields()->ToArray()),$objQuery->GetParameters());
				break;			
			default:
				throw new Exception('Query Type Not Handled');
				break;
		}
		try
		{
			$this->resConnection->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); 
			if($this->objStatement = $this->resConnection->prepare($strQuery))
			{
				$this->objStatement->execute($arrParameters);
				return $this->CheckErrors($this->objStatement, $strQuery);
			}
			else
			{
				//$this->resConnection->debugDumpParams();
				throw new Exception("Prepare statement failed:\r\n".print_r($this->resConnection->errorInfo(), 1)."\r\n".$strQuery);
			}
		}
		catch(PDOException $objException)
		{
			throw new Exception($objException->getMessage());
		}
	}
	
	public function GetInsertId()
	{
		return $this->resConnection->lastInsertId(); 
	}
	
	public function GetAffectedRows()
	{
		return $this->objStatement->rowCount();
	}
	
	protected function GetConnection(){
		if(isset($this->resConnection)){
			return $this->resConnection;
		} else {
			$resConnection = $this->resConnection = $this->Connect();
		}
		return $resConnection;
	}
	
	public function Fetch($strClassName=null, $arrConstructorArgs=array())
	{
		if(!is_null($strClassName))
		{
			$this->objStatement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $strClassName, $arrConstructorArgs);
		}
		else
		{
			$this->objStatement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->objStatement->fetch();
		
	}
	
	public function FetchAll($strClassName=null, $arrConstructorArgs=array())
	{
		if(!is_null($strClassName))
		{
			$this->objStatement->setFetchMode(\PDO::FETCH_CLASS|\PDO::FETCH_PROPS_LATE, $strClassName, $arrConstructorArgs);
		}
		else
		{
			$this->objStatement->setFetchMode(\PDO::FETCH_ASSOC);
		}
		return $this->objStatement->fetchAll();
	}
	
	protected function CheckErrors($objPDO, $strQuery=null)
	{
		$arrErrors = $objPDO->errorInfo();
		if($arrErrors[0] > 0)
		{
			throw new Exception($arrErrors[2].":\r\n".$strQuery);
		}
	}
}
