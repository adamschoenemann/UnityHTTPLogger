<?php
namespace Models;
class Scene extends Model {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "scenes");
	}

	function get_entries_by_event($event)
	{
		if($this->get_child("entries") == NULL)
		{
			// Fetch from db
			$id = $this->get("id");
			$entry = new Entry($this->db);
			$entries =
				$entry->find("scene_id=$id AND event='$event'");
			if($entries == NULL) return array();
			return $entries;
		}
		else
		{
			// Find in entries array
			$entries = $this->get_entries();
			$result = array();
			foreach($entries as &$entry)
			{
				if($entry["event"] == $event)
				{
					$result[] = $entry;
				}
			}
			return $result;
		}
	}

	function calculate_stats()
	{
		$this->set_child("duration", $this->get_duration());
	}

	function get_entries()
	{
		if($this->get_child("entries") == NULL)
		{
			$this->load_entries();
		}
		return $this->get_child("entries");
	}

	function load_entries($sort = true)
	{
		$entry = new Entry($this->db);
		$entries = $entry->find(array("scene_id=?", $this->get("id")));
		if($sort)
		{
			$entries = self::sort_entries($entries);
		}
		$this->set_child("entries", $entries);
	}

	static function sort_entries($entries)
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

	function get_restarts()
	{
		$session_id = $this->get("session_id");
		$name = $this->get("name");
		$q = "
			SELECT * FROM scenes
			WHERE session_id=$session_id
			AND name='$name'
		";
		// echo "id: " . $id . "\n";
		// echo "name: " . $name . "\n";
		// echo "q: " . $q . "\n";

		$scenes = $this->db->exec($q);
		echo count($scenes);
		$restarts = 0;
		foreach($scenes as &$scene)
		{
			if($scene["id"] != $this->get("id") && $scene["time"] != $this->get("time"))
			{
				$restarts++;
			}
		}
		return $restarts;
	}

	function get_general_name()
	{
		return self::s_get_general_name($this->name);
	}

	static function s_get_general_name($name)
	{
		$wolves = array("wolf_sneak", "wolf_kill", "wolf_trap");
		$loonies = array("loonie_race", "loonie_fight", "loonie_puzzle");
		$sheepkings = array("sheepking_simon", "sheepking_fight", "sheepking_shave");

		if(in_array($name, $wolves) || in_array($name, $loonies) || in_array($name, $sheepkings))
		{
			$first = substr($name, 0, strpos($name, "_"));
			$last = "_challenge";
			return $first . $last;
		}
		else
		{
			return $name;
		}
	}

	function get_duration()
	{
		$start = $this->get("start");
		$end = $this->get_end();
		
		$diff = \strtotime($end) - \strtotime($start);
		// echo $start , "\t", $end , "\t", $diff, "\n";
		return $diff;
	}

	function get_help_usage()
	{
		$entries = $this->db->exec(
			"SELECT entries.id, entries.scene_id,
				    int_data.value,  scenes.name, entries.time
			 FROM entries
			 INNER JOIN int_data ON entry_id=entries.id
			 INNER JOIN scenes ON scene_id=scenes.id
			 WHERE  scene_id=?
			 	AND event='HelpMenu'
			 	AND int_data.key='IsOpen'
			 	",
			$this->id
		);
		if(count($entries) < 1)
		{
			return 0;
		}
		
		$helps = array();
		$i = 0;
		foreach($entries as $e)
		{
			if($e["value"] == 1)
			{
				$helps[$i]["start"] = $e["time"];
			}
			else if($e["value"] == 0)
			{
				$helps[$i]["end"] = $e["time"];
				$i++;
			}
		}

		// No end time for help
		if($helps[count($helps) - 1]["end"] === NULL)
		{
			// echo "help with no end<br>";
			$last_entries = $this->get_last_entry();
			$helps[count($helps) - 1]["end"] = $last_entries[0]["time"];
		}
		return $helps;
	}

	function get_last_entry()
	{
		return $this->db->exec("
			SELECT entries.id, entries.timestamp, entries.time
			FROM entries WHERE entries.scene_id=?
			ORDER BY entries.time DESC
			LIMIT 1
		", $this->get("id"));
	}

	function get_end()
	{
		$end = $this->get("end");
		if($end === "0000-00-00 00:00:00") // end is not fine
		{	
			$start = $this->get("start");
			if($this->get("name") == "endgame") // is end game
			{
				// $datestring = str_replace("-", "/", $start);
				// $date = date_parse($datestring);
				// $end = "2013-12-12 00:00:00";
				
				$end = date("Y-m-d H:i:s", strtotime($start . "+1 seconds"));
			}
			else // end not registered
			{
				// get last entry
				$entries = $this->get_last_entry();

				// if entry not found
				if(count($entries) < 1)
				{
					$next = $this->db->exec("
						SELECT id, session_id, start
						FROM scenes
						WHERE id=?
					", $this->id + 1);
					$end = $next[0]["start"];
					// we are at last scene
					if($next["session_id"] != $this->get("session_id"))
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
			
			$this->set("end", $end);
			return $end;
		}
		else // end is fine
		{
			return $end;
		}
	}
	
	
}