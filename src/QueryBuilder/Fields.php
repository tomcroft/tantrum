<?php

namespace TomChaton\ClingDB\QueryBuilder;

class Fields
{

	use TomChaton\ClingDB\Traits\Collection;

	protected $arrFields = array();

	public function __construct()
	{
		$arrData = func_get_args();
		
		foreach($arrData as $mxdField)
		{
			if(is_array($mxdField))
			{
				$this->arrData[array_keys($mxdField)[0]] = array_values($mxdField)[0];
			}
			else
			{
				$this->arrData[$mxdField] = $mxdField;
			}
		}
	}
}
