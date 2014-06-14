<?php
namespace Models;
class Scene extends Model {

	public static function get_entries_by_event($pod, $event)
	{
		$id = $pod["id"];
		// Fetch from db
		$entries = self::db()->exec("SELECT * FROM entries WHERE scene_id=$id AND event='$event'");
		return $entries;
	}

	public static function get_entries($pod)
	{
		$id = $pod["id"];
		$result = self::db()->exec("SELECT * FROM entries WHERE scene_id=$id");
		return $result;
	}


	public static function sort_entries($entries)
	{
		$sorted_entries = array();
		foreach($entries as $entry)
		{
			$event = $entry["event"];
			if(array_key_exists($event, $sorted_entries) == false)
			{
				$sorted_entries[$event] = array();
			}
			$sorted_entries[$event][] = $entry;
		}
		return $sorted_entries;
	}

	public static function get_restarts($session_id, $pod)
	{
		$name = $pod["name"];
		$q = "
			SELECT * FROM scenes
			WHERE session_id=$session_id
			AND name='$name'
		";

		$scenes = self::db()->exec($q);
		$restarts = 0;
		foreach($scenes as &$scene)
		{
			if($scene["id"] != $pod["id"] && $scene["time"] != $pod["time"])
			{
				$restarts++;
			}
		}
		return $restarts;
	}

	public static function get_duration($pod)
	{
		$start = $pod["start"];
		$end = self::get_end($pod);
		
		$diff = \strtotime($end) - \strtotime($start);
		return $diff;
	}

	

	public static function get_last_entry($pod)
	{
		return self::db()->exec("
			SELECT entries.id, entries.timestamp, entries.time
			FROM entries WHERE entries.scene_id=?
			ORDER BY entries.time DESC
			LIMIT 1
		", $pod["id"]);
	}

	/**
	 * May modify $pod["end"]
	 * 
	 * 
	**/
	public static function get_end($pod)
	{
		$end = $pod["end"];
		if($end === "0000-00-00 00:00:00") // end is not fine
		{	
			$start = $pod["start"];
			if($pod["name"] == "endgame") // is end game
				$end = date("Y-m-d H:i:s", strtotime($start . "+1 seconds"));
			else // end not registered
			{
				// get last entry
				$entries = self::get_last_entry($pod);

				// if entry not found
				if(count($entries) < 1)
				{
					$next = self::db()->exec("
						SELECT id, session_id, start
						FROM scenes
						WHERE id=?
					", $pod["id"] + 1);
					$end = $next[0]["start"];
					// we are at last scene
					if($next["session_id"] != $pod["session_id"])
					{
						// just set end to start + 1 second
						$end = date("Y-m-d H:i:s", strtotime($start . "+1 seconds"));
					}
				}
				else // entry was found
				{
					$end = $entries[0]["timestamp"];
				}
			}
			
			$pod["end"] = $end;
			return $end;
		}
		else // end is fine
		{
			return $end;
		}
	}
	
	
}