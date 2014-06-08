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

	
	// Output in different formats
	function output($arr, $options = array())
	{
		// default options
		$options = $options + array("view" => "json", "indent" => true, "pre" => false);

		if($options["view"] == "csv")
		{
			echo \Utils::send_csv(
				\Utils::array_to_csv($arr),
				"output.csv"
			);
		}
		else if($options["view"] == "json")
		{
			if($options["pre"]) echo "<pre>";
			echo \Utils::json_encode($arr, $options["indent"] == null ? false : $options["indent"]);
			if($options["pre"]) echo "</pre>";
		}
		else if($options["view"] == "plain")
		{
			echo \Utils::array_to_csv($arr);
		}
		else if($options["view"] == "table")
		{
			echo \Utils::array_to_table($arr);
		}
		
	}
}