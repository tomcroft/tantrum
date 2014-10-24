<?php

namespace tantrum\QueryBuilder;

class Fields
{

	use \tantrum\Traits\Collection;

	public function __construct()
	{
		$data = func_get_args();
		
		foreach($data as $field)
		{
			if(is_array($field)) {
				$this->data[array_keys($field)[0]] = array_values($field)[0];
			} else {
				$this->data[$field] = $field;
			}
		}
	}
}
