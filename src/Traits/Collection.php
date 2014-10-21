<?php

namespace tomcroft\tantrum\Traits;

trait Collection
{

	protected $data = array();
	
	public function __set($key, $value)
	{
		$this->data[$key] = $value;
	}
	
	public function __get($key)
	{
		return $this->data[$key];
	}
	
	public function Count()
	{
		return count($this->data);
	}
	
	public function IsEmpty()
	{
		return $this->Count() === 0;
	}
	
	public function toArray()
	{
		return $this->data;
	}
}
