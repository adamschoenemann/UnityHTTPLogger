<?php

namespace Controllers;

class ViewData extends Controller
{

	function beforeroute($f3)
	{

	}

	function afterroute($f3)
	{

	}


	function sessions($f3, $params)
	{
		$r = $this->db->exec("SELECT * FROM sessions");
		$this->output($r, $f3->get("GET"));
	}

	function scenes($f3, $params)
	{
		$r = $this->db->exec("SELECT * FROM scenes");
		$this->output($r, $f3->get("GET"));	
	}

	function entries($f3, $params)
	{
		$r = $this->db->exec("SELECT * FROM entries");
		$this->output($r, $f3->get("GET"));	
	}

	/*

	function view_scene($f3, $params)
	{
		$scenes = $this->db->exec("
			SELECT * FROM scenes WHERE id=?
		", $params["id"]);

	}

	function view_session_progress($f3, $params)
	{
		$sessions = $this->db->exec("
			SELECT id, start, end FROM sessions
		");
		foreach($sessions as &$session)
		{
			$scenes = $this->db->exec("
				SELECT id, session_id, name, start, end FROM scenes
				WHERE session_id=?
				ORDER BY time DESC
			", $session["id"]);
			$session["n_scenes"] = count($scenes);
			$session["last_scene"] = $scenes[0]["name"];
		}

		$this->output($sessions, $f3->get("GET"));


	}

	function sort_entries(&$entries)
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

	function get_scene(&$scene)
	{
		$entries = $this->db->exec("
			SELECT * FROM entries WHERE scene_id=?
		", $scene["id"]);

		
		$scene["sorted_entries"] = $this->sort_entries($entries);
	}

	function clean_scenes(&$scenes)
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

	function get_valid_sessions($f3)
	{
		$sessions = $this->db->exec("
			SELECT * FROM sessions
			INNER JOIN scenes ON sessions.id=session_id
			WHERE scenes.name='endgame'
		");
		return $sessions;
	}

	function view_session($f3, $params)
	{

		$id = $params["id"];

		$session = new \Models\Session($this->db);
		$session->load(array("id=?", $id));
		$this->output($session->cast(), $f3->get("GET"));
	

	}

	private function get_choices(&$entries)
	{
		$choices = $this->filter_entries_by_event($entries, "Choice");
		foreach($choices as &$choice)
		{
			$choice["data"] = $this->get_data($choice, "string_data");
		}
		$orders = $this->filter_entries_by_event($entries, "ThreeOptionsOrder");
		foreach($orders as &$order)
		{
			$order["data"] = $this->get_data($order, "string_data");
		}
		return array_map(function($choice, $order){
			return array("scene_name" => $choice["data"][0]["value"],
						 "chosen" => $choice["data"][1]["value"],
						 "order" => array($order["data"][0]["key"] => $order["data"][0]["value"],
						 				  $order["data"][1]["key"] => $order["data"][1]["value"],
						 				  $order["data"][2]["key"] => $order["data"][2]["value"]));
		}, $choices, $orders);
	}

	
	private function filter_entries_by_event(&$entries, $event_name)
	{
		$result = array();
		foreach($entries as $e)
		{
			if($e["event"] == $event_name)
			{
				$result[] = $e;
			}
		}
		return $result;
	}

	private function count_events(&$entries)
	{
		$result = array();
		foreach($entries as &$e)
		{
			$result[$e["event"]] += 1;
		}
		return $result;
	}

	private function get_scenes($session)
	{
		return $this->db->exec("
			SELECT * FROM scenes WHERE session_id=?
		", $session["id"]);
	}

	private function get_all_entries($session)
	{
		return array_reduce($this->get_scenes($session), function($acc, $e){
			return array_merge($acc, $this->get_entries($e));
		}, array());
	}

	private function get_entries($scene)
	{
		return $this->db->exec("
			SELECT * FROM entries WHERE scene_id=? ORDER BY time ASC
		", $scene["id"]);
	}

	private function count_restarts($scenes)
	{
		$prev = NULL;
		$result = array();
		foreach($scenes as &$s)
		{
			if(\array_key_exists($s["name"], $result) === false)
				$result[$s["name"]] = 0;
			if($prev != NULL)
			{
				if($prev["name"] == $s["name"] && $prev["time"] != $s["time"])
				{
					$result[$s["name"]] += 1;
				}
			}
			$prev = $s;
		}
		return $result;
	}

	private function get_duration(&$model)
	{
		$start = $model["start"];
		$end = $model["end"];
		$diff = \strtotime($end) - \strtotime($start);
		return $diff;
	}

	private function get_all_data($entry)
	{
		$tblnames = array("int_data", "float_data", 
						  "string_data", "quaternions",
						  "vector3s");
		$result = array();
		foreach($tblnames as $tblname)
		{
			$data = $this->get_data($tblname);
			if(count($data) > 0)
			{
				$result[$tblname] = $data;
			}
		}
		return $result;
	}

	private function get_data(&$entry, $tblname)
	{
		$q = "SELECT * FROM $tblname WHERE entry_id=" . $entry['id'];
		return $this->db->exec(
			$q	
		);
	}

	private function find_end(&$entries)
	{
		$last = end($entries);
		return $last["timestamp"];
	}

	function get_sessions()
	{
		return $this->db->exec("
			SELECT * FROM sessions;
		");
	}

	function is_returning($session)
	{
		if(empty($session["scenes"]))
			$session["scenes"] = $this->get_scenes($session);
		if(empty($session["entries"]))
			$session["entries"] = $this->get_all_entries($session);
		foreach($session["entries"] as $entry)
		{
			$int_data = $this->get_data($entry, "int_data");
			foreach($int_data as $int)
			{
				if($int["key"] == "returning")
					return $int["val"] == 1 ? true : false;
			}
		}
	}

	function view_with_scenes($f3, $params)
	{
		$sessions = \Models\Session::find_with_scenes(array("wolf_kill", "wolf_finish"));
		\Utils::print_r($sessions);
	}

	function view_stats($f3, $params)
	{
		$get = $f3->get("GET");
		
		// User input processing
		$last_scene = $get["last_scene"] ? $get["last_scene"] : "endgame";
		if($get["include"])
			$included = explode(",", str_replace(" ", "", $get["include"]));
		if($get["exclude"])
			$excluded = explode(",", str_replace(" ", "", $get["exclude"]));
		if($get["order"])
			$order = explode(",", str_replace(" ", "", $get["order"]));
		if($get["decimals"])
			$decimals = intval($get["decimals"]);

		// Data fetching ---------------
		$sessions = $this->db->exec(
			"SELECT session_id AS id FROM scenes
			 WHERE scenes.name='$last_scene'
			 GROUP BY session_id
			 ORDER BY session_id
		", $last_scene);
		
		$session = new \Models\Session($this->db);

		$tmp_sessions = $sessions;
		$sessions = array();

		// Data filtering
		// Filter returning out
		foreach($tmp_sessions as $i => &$s)
		{
			$session->load(array("id=?", $s["id"]));
			if($session->is_returning() == false)
			{
				$sessions[] = $s;
			}
			$session->reset();
		}

		$results = array();
		$cache = array();

		
		// Results computing
		$statfuns = array(
			"entries" => function($i, $s) {
				$entries = $db->exec("SELECT * FROM entries");
				echo "hey";
				return array("entries" => $entries);
			},
			"OCEAN" => function($i, $s) {
				if($cache["avgs"] != NULL)
					return $cache["avgs"];

				$db = \Base::instance()->get("db");
				$respondents = $db->exec("SELECT * FROM answers WHERE session_id=?", $s["id"]);
				$answers = Questionnaire::parse_answers($respondents[0]["answers"]);	
				$cache["avgs"] = $answers["avgs"];
				return $answers["avgs"];
			},
			"demographics" => function($i, $s) {
				return $s->get_demographics();
			},
			"choices" => function($i, $s) {
				$choices = $s->get_choices();
				$named_choices = array();
				for($j = 0, $len = count($choices); $j < $len; $j++)
				{
					$choice = $choices[$j];
					$named_choices["choice_" . $j] = $choice;
				}
				return $named_choices;
			},
			"durations" => function($i, $s) use($cache) {
				if($cache["durations"] == NULL)
					$cache["durations"] = $s->get_scene_durations();
				return $cache["durations"];
			},
			"total_duration" => function($i, $s) use($cache) {
				if($cache["durations"] == NULL)
					$cache["durations"] = $s->get_scene_durations();
				return $s->get_duration($cache["durations"]);
			},
			"total_option_duration" => function($i, $s) use($cache) {
				if($cache["durations"] == NULL)
					$cache["durations"] = $s->get_scene_durations();
				return $s->get_total_option_duration($cache["durations"]);
			},
			"n_collectibles" => function($i, $s) {
				return $s->get_n_collectibles();
			},
			"n_help" => function($i, $s) {
				return $s->get_n_times_need_help();
			},
			"total_help_time" => function($i, $s) {
				return $s->get_total_time_in_help();
			},
			"shave_stats" => function($i, $s) {
				return $s->get_shave_stats();
			},
			"options_order" => function($i, $s) {
				return $s->get_options_order();
			},
			"answers" => function($i, $s) {
				$db = \Base::instance()->get("db");
				$answers_row = $db->exec(
					"SELECT answers FROM answers WHERE session_id=?", $s->id
				);
				$raw_answers = $answers_row[0]["answers"];
				$answers = explode("|", substr($raw_answers, 0, strlen($raw_answers) - 1));
				foreach($answers as $j => $a)
				{
					$keyed_answers["q" . strval($j + 1)] = $a;
				}
				return $keyed_answers;
			},
			"restarts" => function($i, $s) {
				return $s->count_restarts();
			},
			"challenges" => function($i, $s) {
				return $s->get_challenges();
			},
			"challenges_completed" => function($i, $s) {
				return $s->get_finish_scenes();
			},
			"questionnaire_time" => function($i, $s) {
				return $s->get_questionnaire_time();
			},
			"feedback" => function($i, $s) {
				return $s->get_feedback();
			},
			"ordered_OCEAN" => function($i, $s) {
				if($cache["avgs"] != NULL)
					return $cache["avgs"];

				$db = \Base::instance()->get("db");
				$respondents = $db->exec("SELECT * FROM answers WHERE session_id=?", $s["id"]);
				$answers = Questionnaire::parse_answers($respondents[0]["answers"]);	
				$cache["avgs"] = $answers["avgs"];
				$ordered = Questionnaire::sort_avgs($answers["avgs"]);
				$result = array();
				$j = 1;
				foreach($ordered as $k => $o)
				{
					$result["trait_" . $j] = $k;
					$result["trait_" . $j . "_value"] = $o;
					$j++;
				}
				return $result;
			}

		);
		

		$to_compute = array();
		if($included[0] == "all") // display all
		{
			$to_compute = $statfuns;
		}
		else if($included) // display included
		{
			foreach($included as $inc)			
			{
				if($statfuns[$inc])
				{
					$to_compute[$inc] = $statfuns[$inc];
				}
				else
				{
					die("stat " . $inc . " does not exist");
				}
			}
		}
		else if($excluded) // display not exluded
		{
			foreach($statfuns as $k => $f)
			{
				if(in_array($k, $excluded) == false)
				{
					$to_compute[$k] = $f;
				}
			}
		}
		else
		{
			// show help
			echo "No stats included or exluded!<br>
				  Use include og exclude params to filter data, <br>
				  e.g. /stats?view=table?include=demographics,OCEAN<br>
				  Possible fields are: <br>";
			echo "all", "<br>";
			foreach($statfuns as $k => $f)
			{
				echo $k, "<br>";
			}
			echo "You can also filter the sessions by last scene using the
			last_scene=[scene_name], e.g. last_scene=wolf_main<br>";
			echo "You can also order the results with order=[field],[ASC|DESC]<br>";
			echo "You can specify the format in which to view the data with the
			view parameter, e.g. ?view=[table|json|plain|csv]";
			die();
		}

		// Results aggregation and post-processing
		foreach($sessions as $i => $record)
		{
			$session->reset();
			$session->load(array("id=?", $record["id"]));
			$row = array();
			$row["id"] = $record["id"];

			foreach($to_compute as $k => $f)
			{
				$val = $f($i, $session);
				if(is_array($val))
				{
					$row = array_merge($row, $val);
				}
				else
				{
					$row[$k] = $val;
				}
			}
			if($decimals) // decimal formatting
			{
				foreach($row as &$v)
				{
					if(is_float($v))
						$v = number_format($v, $decimals);
				}
			}

			$results[] = $row;

		}
		// echo $order[0], ", ", $order[1], "<br>";
		if($order)
		{
			echo "sorting, ". $order[1] . "<br>";
			usort($results, function($a, $b){
				$c = $a["questionnaire_time"];
				$d = $b["questionnaire_time"];
				// echo $c, " ", $d, "<br>";
				if($c == $d) return 0;
				
				if($order[1] == NULL || $order[1] === "ASC")
					return ($c > $d) ? 1 : -1;
				else if($order[1] === "DESC")
					return ($c < $d) ? 1 : -1;
			});
		}

		// Output
		$this->output($results, $get);
	}
	*/

}