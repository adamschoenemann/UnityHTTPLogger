<?php
namespace Models;
class Session extends Model {

	public static function get_scenes($pod)
	{
		$id = $pod["id"];
		return self::db()->exec("SELECT * FROM scenes WHERE session_id=$id");
	}

	public static function clean_scenes($scenes)
	{
		$prev = NULL;
		$result = array();
		foreach($scenes as &$s)
		{
			if($prev != NULL)
			{
				if($prev["time"] == $s["time"])
				{
					array_pop($result);
				}
			}
			$result[] = $s;
			$prev = $s;
		}
		return $result;
	}


	function is_completed()
	{
		$scenes = self::get_scenes();
		foreach($scenes as $scene)
		{
			if($scene["name"] == "endgame")
				return true;
		}
		return false;
	}


	public static function get_entries($pod)
	{
		$id = $pod["id"];
		$entries = self::db()->exec("
			SELECT entries.*, scenes.session_id
			FROM entries 
			INNER JOIN scenes on entries.scene_id=scenes.id
			WHERE scenes.session_id=?
		", $id);
		
		return $entries;
	}

	static function find_with_scenes($scene_names)
	{	
		$subq_template = "SELECT session_id, scenes.name FROM scenes
			    WHERE scenes.name='{{scene_name}}'
			    GROUP BY session_id
			    ";

		$subqs = array();
		foreach($scene_names as $n)
		{
			$subqs[] = str_replace("{{scene_name}}", $n, $subq_template);
		}
		$subquery = implode("UNION\n", $subqs);
		// echo $subqs, "<br>";
		$q_template = "SELECT
			  tbl1.session_id, tbl1.name, COUNT(tbl1.session_id) AS finished
			FROM
			(
			    {{subqueries}}
			) AS tbl1
			GROUP BY tbl1.session_id
			ORDER BY tbl1.session_id
		";

		$q = str_replace("{{subqueries}}", $subquery, $q_template);
		echo $q, "<br>";
		$rows = self::db()->exec($q, $scene_name);
		return $rows;
	}

	/**
	 * May modify $pod["end"]
	 *
	 */
	public static function get_duration($pod)
	{
		$start = $pod["start"];
		$end = $pod["end"];
		if($end === "0000-00-00 00:00:00")
		{
			$entries = self::db()->exec("
				SELECT entries.id, entries.timestamp, entries.time, scenes.session_id
				FROM entries 
				INNER JOIN scenes on entries.scene_id=scenes.id
				WHERE scenes.session_id=?
				ORDER BY id DESC
				LIMIT 1
			", $pod["id"]);
			$end = $entries[0]["timestamp"];
			$pod["end"] = $end;

		}

		$diff = \strtotime($end) - \strtotime($start);
		return $diff;
	}

	function get_entries_by_event($pod, $event)
	{
		$entries = array();
		$scenes = self::get_scenes($pod);
		foreach($scenes as &$scene)
		{
			$scene_entries = Scenes::get_entries_by_event($pod, $event);
			if(count($scene_entries) < 1) continue;
			$entries = array_merge(
				$entries,
				$scene_entries
			);
		}

		return $entries;
	}


}