<?php
namespace API;
class API {

	protected $db;
	protected $f3;

	function __construct(){
		$f3 = \Base::instance();
    	// Connect to the database
		$db = new \DB\SQL(
			$f3->get("db"),
			$f3->get("admin"),
			$f3->get("pass")
			);
		$this->db = $db;
		$this->f3 = $f3;
	}

	
}