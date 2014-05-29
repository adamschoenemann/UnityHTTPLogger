<?php

namespace Controllers;

class Sessions extends Controller
{

	function beforeroute($f3)
	{

	}

	function afterroute($f3)
	{

	}

	function view_all($f3, $params)
	{
		$model = new \Models\Session($this->db);
		$models = $this->db->exec("
			SELECT id, app_version, start, end FROM sessions
		");
		foreach($models as &$model)
		{
			$scenes = $this->db->exec("
				SELECT COUNT(id) as size FROM scenes WHERE session_id=?
			", $model["id"]);
			$model["n_scenes"] = $scenes[0]["size"];

		}

		$f3->set("models", $models);
		$f3->set("table_headers", array_keys($models[0]));
		echo \Template::instance()->render("sessions.html");
	}

	function view($f3, $params)
	{
		$id = $params["id"];
		$session = $this->get_session_data($f3, $params);
		// $stats = array(
		// 	"n_scenes" => count($session["scenes"]),
		// 	"n_entries" => count(array_reduce(
		// 		function($v, $w){
		// 			$v += count($w["entries"]);
		// 			return $v;
		// 		}, $session["scenes"])
		// );
		echo \Utils::json_encode($session, true);
	}

	function view_json($f3, $params)
	{
		$session = $this->get_session_data($f3, $params);
		echo "<pre>";
		echo \Utils::json_encode($session, true);
		echo "</pre>";
	}

	function get_session_data($f3, $params)
	{
		$id = $params["id"];
		// $model = new \Models\Session($this->db);
		// $model->load(array("id=?", $id));
		// $f3->set("model", $model);
		// $f3->set("table_headers", array_keys($model->cast()));
		$sessions = $this->db->exec("
			SELECT * FROM sessions WHERE id=?
		", $id);

		$session = $sessions[0];

		$scenes = $this->db->exec("
			SELECT id, name FROM scenes WHERE session_id=?
		", $id);

		foreach($scenes as &$scene)
		{
			$loggables = $this->db->exec("
				SELECT id, name FROM loggables WHERE scene_id=?
			", $scene["id"]);
			$scene["loggables"] = $loggables;

			$entries = $this->db->exec("
				SELECT id, event FROM entries WHERE scene_id=?
			", $scene["id"]);
			$scene["entries"] = $entries;

			foreach($scene["entries"] as &$entry)
			{
				$entry["strings"] = $this->db->exec("
					SELECT string_data.key, value FROM string_data WHERE entry_id=?
				", $entry["id"]);

				$entry["ints"] = $this->db->exec("
					SELECT int_data.key, value FROM int_data WHERE entry_id=?
				", $entry["id"]);

				$entry["floats"] = $this->db->exec("
					SELECT float_data.key, value FROM float_data WHERE entry_id=?
				", $entry["id"]);

				$entry["vector3s"] = $this->db->exec("
					SELECT vector3.key, x, y, z FROM vector3 WHERE entry_id=?
				", $entry["id"]);

				$entry["quaternions"] = $this->db->exec("
					SELECT quaternion.key, x, y, z, w FROM quaternion WHERE entry_id=?
				", $entry["id"]);
			}
		}

		$session["scenes"] = $scenes;

		return $session;
		
	}

	private function get_data($tabel, $entry_id)
	{
		return "SELECT * FROM $tabel WHERE entry_id=$entry_id";
	}

}