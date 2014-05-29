<?php
namespace Models;
class Session extends Model {

	private $entry_cache = NULL;

	function __construct(\DB\SQL $db){
		parent::__construct($db, "sessions");
	}

	static function get_valid($db)
	{
		$f3 = \Base::instance();
		$session_pods = $db->exec("
			SELECT sessions.id AS id FROM sessions
			INNER JOIN scenes ON sessions.id=session_id
			WHERE scenes.name='endgame'
		");

		$result = array();
		foreach($session_pods as &$session_pod)
		{
			$session = new Session($db);
			$session->load(array("id=?", $session_pod["id"]));
			// print_r($session->cast());
			// if($session->is_returning() === false)
			// {
				$result[] = $session;
			// }
		}

		
		return $result;
	}

	function get_scenes()
	{
		if($this->get_child("scenes") == NULL)
		{
			$this->load_scenes();
		}
		return $this->get_child("scenes");
	}

	static function clean_scenes($scenes)
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

	function fetch_scenes()
	{
		$scene = new Scene($this->db);
		return $scene->find(array("session_id=?", $this->get("id")),
			array("order" => 'id'));
	}

	function load_scenes()
	{
		$scenes = $this->fetch_scenes();
		$clean_scenes = self::clean_scenes($scenes);
		$this->set_child("scenes", $clean_scenes);
	}

	function is_completed()
	{
		$scenes = $this->get_scenes();
		foreach($scenes as $scene)
		{
			if($scene["name"] == "endgame")
				return true;
		}
		return false;
	}

	function get_entries_pod()
	{
		// $scenes = $this->get_scenes();
		// $q = "SELECT * FROM entries WHERE scene_id IN(";
		// foreach($scenes as &$scene)
		// {
		// 	$q .= $scene["id"] . ",";
		// }
		// $q = substr($q, 0, strlen($q) - 1);
		// $q .= ")";
		// $entries = $this->db->exec($q);
		
		return self::get_entries_pod($this->get("id"));
	}

	public static function s_get_entries_pod($id)
	{
		$entries = $this->db->exec("
			SELECT entries.*, scenes.session_id
			FROM entries 
			INNER JOIN scenes on entries.scene_id=scenes.id
			WHERE scenes.session_id=?
		", $id);
		
		return $entries;
	}

	static function find_with_scenes($scene_names)
	{	
		$db = \Base::instance()->get("db");
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
		$rows = $db->exec($q, $scene_name);
		return $rows;
	}

	function get_entries()
	{
		if($this->entry_cache !== NULL)
		{
			return $this->entry_cache;
		}
		$scenes = $this->get_scenes();
		$entries = array();
		foreach($scenes as $scene)
		{
			$entries = array_merge($entries, $scene->get_entries());
		}
		$this->entry_cache = $entries;
		return $entries;
	}

	function get_options_order()
	{
		$q = "SELECT session_id, entry_id, 
					 scene_id, string_data.key, value, event
			  FROM entries
			  INNER JOIN string_data ON entry_id=entries.id
			  INNER JOIN scenes ON scene_id=scenes.id
			  INNER JOIN sessions ON session_id=sessions.id
			  WHERE sessions.id=?
			    AND event='ThreeOptionsOrder'
			    AND string_data.key LIKE 'option_0%'
			  ORDER BY scene_id, string_data.key
			 ";
		$result = array(
			"wolf_option_00" => "",
			"wolf_option_01" => "",
			"wolf_option_02" => "",
			"loonie_option_00" => "", 
			"loonie_option_01" => "", 
			"loonie_option_02" => "", 
			"sheepking_option_00" => "",
			"sheepking_option_01" => "",
			"sheepking_option_02" => ""
		);
		$entries = $this->db->exec($q, $this->id);

		foreach($entries as $entry)
		{
			$val = $entry["value"];
			$key = $entry["key"];
			$splits = explode("_", $val);
			$choice = $splits[0];
			$index = $choice . "_" . $key;
			$result[$index] = $val;
		}
		return $result;
	}

	function get_duration($scenes = NULL)
	{
		if($scenes)
		{
			return array_sum($scenes);
		}
		else // Not sure this is working as intented, but do we need it?
		{
			$start = $this->get("start");
			$end = $this->get("end");
			if($end === "0000-00-00 00:00:00")
			{
				$entries = $this->db->exec("
					SELECT entries.id, entries.timestamp, entries.time, scenes.session_id
					FROM entries 
					INNER JOIN scenes on entries.scene_id=scenes.id
					WHERE scenes.session_id=?
					ORDER BY id DESC
					LIMIT 1
				", $this->get("id"));
				$end = $entries[0]["timestamp"];
				$this->set("end", $end);

			}

			$diff = \strtotime($end) - \strtotime($start);
			return $diff;
		}
	}

	function get_shave_n_wrong_presses()
	{
		$q = "SELECT entries.id, scenes.id
			  FROM entries
			  INNER JOIN scenes ON scenes.id=entries.scene_id
			  INNER JOIN sessions ON scenes.session_id=sessions.id
			  WHERE session_id=?
			    AND scenes.name='sheepking_shave'
			  	AND event IN('MissedButton', 'WrongButton', 'NoButtonNeeded')
			  ";
		$entries = $this->db->exec($q, $this->id);
		return count($entries);
	}

	function get_shave_n_correct_presses()
	{
		$q = "SELECT entries.id, scenes.id
			  FROM entries
			  INNER JOIN scenes ON scenes.id=entries.scene_id
			  INNER JOIN sessions ON scenes.session_id=sessions.id
			  WHERE session_id=?
			    AND scenes.name='sheepking_shave'
			  	AND event='CorrectButton'
			  ";
		$entries = $this->db->exec($q, $this->id);
		return count($entries);
	}

	function get_shave_stats()
	{
		$correct = $this->get_shave_n_correct_presses();
		$wrong = $this->get_shave_n_wrong_presses();
		return array(
			"shave_correct" => $correct,
			"shave_wrong" => $wrong,
			"shave_ratio" => $wrong / ($correct + $wrong)
		);
	}

	function get_total_option_duration($durations)
	{
		$result = 0;
		foreach($durations as $name => $dur)
		{
			if(strpos($name, "option"))
			{
				$result += $dur;
			}
		}
		return $result;
	}

	function get_age()
	{
		$id = $this->get("id");
		$age = $this->db->exec("
			SELECT age FROM answers WHERE session_id=?",
			$id);
		return $age[0];
	}

	function get_demographics()
	{
		$id = $this->get("id");
		$result = $this->db->exec(
			"SELECT gender, age, nationality FROM answers
			 WHERE session_id=?",
			$id
		);
		return $result[0];
	}

	/**
	 * Returns scene durations
	 * This new implementation uses the "time" field for in-game time instead of
	 * real time. This means that pauses are not accounted for. Also means that
	 * endgame is not counted, which imho is fine
	 * @return [type] [description]
	 */
	function get_scene_durations()
	{
		$scenes = $this->get_scenes();
		$results = array();

		for($i = 0, $len = count($scenes); $i < $len - 1; $i++)
		{
			$scene = $scenes[$i];
			$next_scene = $scenes[$i + 1];
			$dur = $next_scene->time - $scene->time;
			$results[$scene->get_general_name() . "_dur"] += $dur;
		}

		if($results["pregame_dur"] == NULL)
		{
			$results = array_merge(array(
				"pregame_dur" => 1
			), $results);
		}
		return $results;

	}

	// function get_scene_durations()
	// {
	// 	$scenes = $this->get_scenes();
	// 	$results = array();
	// 	// printf("%s\t%10s\n", "hey", "you");
	// 	// printf("%s %2s %3s %22s %22s %22s\n", "session_id", "id", "name", "start", "end", "dur");
	// 	// printf("%s %3s %3s %8s %16s %22s %32s %38s\n",
	// 		// "session_id", "id", "name", "start", "end", "dur");

	// 	foreach($scenes as &$scene)
	// 	{	
	// 		$results[$scene->get_general_name() . "_dur"] += $scene->get_duration();
	// 	}
	// 	if(array_key_exists("pregame_dur", $results) === false)
	// 	{
	// 		// echo "no pregame on " . $this->get("id") . "<br>";
	// 		$results = array_merge(array(
	// 			"pregame_dur" => 1
	// 		), $results);
	// 	}
	// 	return $results;
	// 	// print_r($results);
	// }


	function get_entries_by_event($event)
	{
		$entries = array();
		$scenes = $this->get_scenes();
		foreach($scenes as &$scene)
		{
			$scene_entries = $scene->get_entries_by_event($event);
			// print_r($scene_entries);	
			if(count($scene_entries) < 1) continue;
			$entries = array_merge(
				$entries,
				$scene_entries
			);
		}

		return $entries;
	}

	function get_n_times_need_help()
	{
		$entries = $this->db->exec(
			"SELECT COUNT(entries.id) AS total_help, sessions.id AS session_id, entries.id, entries.scene_id,
				    int_data.value,  scenes.name
			 FROM entries
			 INNER JOIN int_data ON entry_id=entries.id
			 INNER JOIN scenes ON scene_id=scenes.id
			 INNER JOIN sessions ON scenes.session_id=sessions.id
			 WHERE  scenes.session_id=? 
			 	AND event='HelpMenu' 
			 	AND int_data.key='IsOpen'
			 	AND int_data.value=1
			 	",
			$this->id
		);
		return $entries[0]["total_help"];
	}

	function get_total_time_in_help()
	{
		$scenes = $this->get_scenes();
		$time = 0;
		foreach($scenes as &$scene)
		{
			$helps = $scene->get_help_usage();
			foreach($helps as $help)
			{
				$dur = floatval($help["end"]) - floatval($help["start"]);
				$time += $dur;
			}
		}
		return $time;
	}

	function get_finish_scenes()
	{
		$scenes = $this->db->exec(
			"SELECT * FROM scenes
			 WHERE session_id=?
			   AND name LIKE '%finish'
		", $this->id);
		$results = array(
			"wolf_finish" => 0,
			"loonie_finish" => 0,
			"sheepking_finish" => 0
		);

		foreach($scenes as $scene)
		{
			$n = $scene["name"];
			if($n === "game_finish")
				$n = "sheepking_finish";
			$results[$n] = 1;
		}
		return $results;
	}

	function get_questionnaire_time()
	{
		$scenes = $this->get_scenes();
		$start = 0;
		$end = 0;
		foreach($scenes as $scene)
		{
			$n = $scene->name;
			if($n === "personality_test")
			{
				$start = $scene->time;
			}
			else if($n === "game_intro")
			{
				$end = $scene->time;
				break;
			}
		}
		return ($end - $start);
	}

	function get_challenges()
	{
		$scenes = $this->get_challenge_scenes();
		// \Utils::print_r($scenes);
		$model = new Scene($this->db);
		$results = array(
			"wolf_challenge"      => "",
			"loonie_challenge"    => "",
			"sheepking_challenge" => ""
		);
		foreach ($scenes as $scene)
		{
			$model->reset();
			$model->load(array("id=?", $scene["id"]));
			$results[$model->get_general_name()] = $model->name;
		}
		return $results;
	}

	function get_challenge_scenes()
	{
		return $this->db->exec(
			"SELECT * FROM scenes 
			 WHERE session_id=?
			 AND name IN ('wolf_sneak', 'wolf_kill', 'wolf_trap',
		  	              'loonie_puzzle', 'loonie_fight', 'loonie_race',
				          'sheepking_fight', 'sheepking_shave', 'sheepking_simon')
			 GROUP BY name
			", $this->id);
	}

	function get_n_collectibles()
	{
		$collected = $this->db->exec(
			"SELECT COUNT(entries.id) AS collected
			 FROM entries 
			 INNER JOIN scenes on entries.scene_id=scenes.id
			 WHERE scenes.session_id=?
			 AND entries.event='CollectiblePickUp'",
			 $this->id
		);
		// echo $collected, "<br>";
		return $collected[0]["collected"];
	}



	function get_choices()
	{
		$scenes = $this->get_scenes();
		$choices = array();
		$not_choices = array("main", "three", "intro", "finish");
		foreach($scenes as &$scene)
		{
			$splits = explode("_", $scene["name"]);
			if($splits[0] == "wolf")
			{
				if(in_array($splits[1], $not_choices) == false && empty($choices[0]))
				{
					$choices[0] = $scene["name"];
				}
			}
			else if($splits[0] == "loonie")
			{
				if(in_array($splits[1], $not_choices) == false && empty($choices[1]))
				{
					$choices[1] = $scene["name"];
				}
			}
			else if($splits[0] == "sheepking")
			{
				if(in_array($splits[1], $not_choices) == false && empty($choices[2]))
				{
					$choices[2] = $scene["name"];
				}
			}
		}
		return $choices;
	}


	function calculate_stats()
	{	
		$n_scenes = count($this->get_scenes());
		$n_entries = count($this->get_entries());

		$this->set_child("n_scenes", $n_scenes);
		$this->set_child("n_entries", $n_entries);

		$this->set_child("restarts", $this->count_restarts());
		$this->set_child("duration", $this->get_duration());
		$scenes = $this->get_child("scenes");
		foreach($scenes as &$scene)
		{
			$scene->calculate_stats();
		}
	}

	function get_feedback()
	{
		$fb = $this->db->exec(
			"SELECT string_data.key, string_data.value FROM entries
			INNER JOIN scenes ON entries.scene_id=scenes.id
			INNER JOIN sessions ON scenes.session_id=sessions.id
			INNER JOIN string_data ON string_data.entry_id=entries.id
			WHERE sessions.id=?
			  AND event='PostGameFeedback'
			  AND string_data.key='Feedback'
			GROUP BY sessions.id
			", $this->id
		);
		return '"'. str_replace(str_split("\r\n\t"), " ", $fb[0]["value"] . '"');
	}

	function count_restarts()
	{
		$q = "SELECT id, session_id, (COUNT(id) - 1) AS restarts, name
			  FROM
				(SELECT * FROM scenes
				WHERE session_id=?
				AND name IN ('wolf_sneak', 'wolf_kill', 'wolf_trap',
				             'loonie_puzzle', 'loonie_fight', 'loonie_race',
				             'sheepking_fight', 'sheepking_shave', 'sheepking_simon')
				GROUP BY time) AS tbl1
			  GROUP BY name
			  ORDER BY id
			  ";
		$rows = $this->db->exec($q, $this->id);
		$results = array();
		$categories = array(
			"restarts_by_game" => 0,
			"restarts_by_user" => 0
		);
		foreach($rows as $row)
		{
			$results[Scene::s_get_general_name($row["name"]) . "_restarts"] = 
				$row["restarts"];
			if(preg_match("#kill|fight|sneak|trap#", $row["name"], $matches)) // if aggressive level
			{
				$categories["restarts_by_game"] += $row["restarts"];
			}
			else
			{
				$categories["restarts_by_user"] += $row["restarts"];
			}
		}
		return array_merge($results, $categories);
	}

	// untested
	function get_data($keyname, $tblname)
	{
		$entries = $this->get_entries();
		$data = array();
		foreach($entries as $entry)
		{
			$data = array_merge($data,
				$entry->get_data($keyname, $tblname));
		}
		return $data;
	}

	function is_returning()
	{
		$data = $this->db->exec("
			SELECT entries.*, scenes.session_id, int_data.value
			FROM entries 
			INNER JOIN scenes on entries.scene_id=scenes.id
			INNER JOIN int_data ON entries.id=int_data.entry_id
			WHERE scenes.session_id=?
			AND entries.event='IsReturning'
		");
		return $data["value"] == 1 ? true : false;
	}

	function load_entries()
	{
		$scenes = $this->get_scenes();
		foreach($scenes as $scene)
		{
			$scene->load_entries();
		}
	}

	
}