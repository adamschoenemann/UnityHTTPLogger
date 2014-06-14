<?php
namespace Models;
class Entry extends Model
{

	public static $tables = array("vector3", "quaternion",
								 "string_data", "int_data",
								 "float_data");



	public static function load_all_data($pod)
	{
		$id = $pod["id"];
		$result = array();
		foreach (self::tables as $table)
		{
			$result[] = self::load_data($id, $table);
		}
	}

	public static function load_data($pod, $tblname)
	{	
		$id = $pod["id"];
		$data = self::db()->exec("
			SELECT * FROM $tblname WHERE entry_id=$id
		");
		return $data;
	}

	public static function get_data_by_key($pod, $keyname, $tblname)
	{
		$id = $pod["id"];
		// Fetch from db
		$data = self::db()->exec("
			SELECT * FROM $tblname
			WHERE entry_id=$id AND $tblname.key='$keyname'
		");
		if(count($data) < 1)
			return NULL;
		return $data;

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

	
	
}