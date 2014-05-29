<?php
namespace Models;
abstract class Model extends \DB\SQL\Mapper {

	private $children = array();

	public function get_child($child_name)
	{
		if(array_key_exists($child_name, $this->children))
			return $this->children[$child_name];
		else return NULL;
	}

	public function set_child($child_name, $val)
	{
		$this->children[$child_name] = $val;
	}

	public function reset()
	{
		parent::reset();
		$this->children = array();
	}

	public function cast($object = NULL)
	{
		$r = parent::cast($object);
		if(count($this->children) > 0)
		{
			foreach($this->children as $key => &$child)
			{
				if($child === NULL)
					continue;
				if(is_array($child))
				{
					if(method_exists($child[0], "cast"))
					{
						$r[$key] = array();
						foreach($child as $mapper)
						{
							$r[$key][] = $mapper->cast();
						}		
					}
					else
					{
						$r[$key] = $child;
					}
				}
				else if(is_scalar($child))
				{
					$r[$key] = $child;
				}
				
			}
		}
		return $r;
	}
	
	
}