<?php
namespace Controllers;

class Controller
{
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
		$f3->set("db", $db);
		$this->db = $db;
		$this->f3 = $f3;
	}

	

	function output($arr, $get, $indent = true)
	{
		// Output in different formats
		if($get["view"] == "csv")
		{
			echo \Utils::send_csv(
				\Utils::array_to_csv($arr),
				"stats.csv"
			);
		}
		else if($get["view"] == "json")
		{
			echo \Utils::json_encode($arr, $indent);
		}
		else if($get["view"] == "plain")
		{
			echo \Utils::array_to_csv($arr);
		}
		else if($get["view"] == "table")
		{
			echo \Utils::array_to_table($arr);
		}
		else
		{
			echo \Utils::json_encode($arr, $indent);
		}
	}
}