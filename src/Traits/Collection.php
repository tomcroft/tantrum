<?php

namespace TomChatin\clingDB\Traits;

trait Collection
{

	protected $arrData = array();
	
	public function __set($mxdKey, $mxdValue)
	{
		$this->arrData[$mxdKey] = $mxdValue;
	}
	
	public function __get($mxdKey)
	{
		return $this->arrData[$mxdKey];
	}
	
	public function Count()
	{
		return count($this->arrData);
	}
	
	public function IsEmpty()
	{
		return $this->Count() === 0;
	}
	
	public function ToArray()
	{
		return $this->arrData;
	}
}
