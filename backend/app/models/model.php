<?php
namespace Models;

class Model
{
	private static $db = NULL;
	private static $f3 = NULL;

	public function db(){
		if(self::$f3 == NULL)
			self::$f3 = \Base::instance();
    	// Connect to the database
    	if(self::$db == null)
    	{
			self::$db = new \DB\SQL(
				$f3->get("db"),
				$f3->get("admin"),
				$f3->get("pass")
			);
    	}
		return self::$db;
	}
}