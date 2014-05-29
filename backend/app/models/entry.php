<?php
namespace Models;
class Entry extends Model
{

	public static $tables = array("vector3", "quaternion",
								 "string_data", "int_data",
								 "float_data");

	function __construct(\DB\SQL $db){
		parent::__construct($db, "entries");
	}

	public function load_all_data()
	{
		$id = $this->get("id");
		
		foreach (self::tables as $table)
		{
			$this->load_data($table);
		}
	}

	public function load_data($tblname)
	{	
		$id = $this->get("id");
		$data = $this->db->exec("
			SELECT * FROM $tblname WHERE entry_id=$id
		");
		// echo $tblname . "\n";
		// echo $this->get("id") . "\n";
		// echo count($data);
		$this->set_child($tblname, $data);
	}

	public function get_data_by_key($keyname, $tblname)
	{
		if($this->get_child($tblname) == NULL)
		{
			// Fetch from db
			$id = $this->get("id");
			$data = $this->db->exec("
				SELECT * FROM $tblname
				WHERE entry_id=$id AND $tblname.key='$keyname'
			");
			if(count($data) < 1)
				return NULL;
			return $data;

		}
		else
		{
			// Get from data
			$data = $this->get_data($tblname);
			$result = self::filter_data_by_key($data, $keyname);
			if(count($result) < 1)
				return NULL;

			return $result;
		}
	}

	public static function filter_data_by_key($data, $keyname)
	{
		$result = array();
		foreach($data as &$d)
		{
			if($d["key"] == $keyname)
				$result[] = $d;
		}

		return $result;
	}

	public function get_data($tblname)
	{
		if($this->get_child($tblname) === NULL)
		{
			$this->load_data($tblname);
		}
		return $this->get_child($tblname);
	}

	
	
}