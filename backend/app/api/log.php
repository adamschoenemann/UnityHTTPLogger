<?php
namespace API;
class Log extends API {

	public function start_session($f3, $params){
		$post = $f3->get("POST");
		// retry test
	/*	if(intval($post["retries"]) > 0)
		{
			$f3->error(500);
			return;
		}*/
		if(empty($post["app_version"])){
			echo "no app version specified";
			return;
		}

		$this->db->begin();

		// $session = new \Models\Session($this->db);
		// $session->app_version = $post["app_version"];
		// $session->MAC = hash("SHA256", $post["MAC"]);
		// $session->ip = hash("SHA256", $_SERVER['REMOTE_ADDR']);
		// $session->save();
		$app_version = $post["app_version"];
		$MAC = hash("SHA256", $post["MAC"]);
		$ip = hash("SHA256", $_SERVER["REMOTE_ADDR"]);
		$this->db->exec("
			INSERT INTO sessions
				(app_version, MAC, ip)
				VALUES (:app_version, :MAC, :ip)
			",
			array(":app_version" => $app_version,
				  ":MAC" => $MAC,
				  ":ip" => $ip)
		);
		
		$session = $this->db->exec("
			SELECT * FROM sessions
			ORDER BY id DESC
			LIMIT 1
		");

		$this->db->commit();
		echo \Utils::json_encode(array("id" => intval($session[0]["id"])));
	}

	private function save_data($entry, $key, $entry_model, $data_model)
	{
		if(empty($entry[$key]))
			return;

		foreach($entry[$key] as $data)
		{
			$data_model->key = $data["key"];
			$data_model->value = $data["value"];
			$data_model->entry_id = $entry_model->id;
			$data_model->save();
			$data_model->reset();
		}
	}

	private function save_vector3($entry, $key, $entry_model, $data_model)
	{
		if(empty($entry[$key]))
			return;
		
		foreach($entry[$key] as $data)
		{
			$data_model->key = $data["key"];
			$data_model->x = $data["x"];
			$data_model->y = $data["y"];
			$data_model->z = $data["z"];
			$data_model->entry_id = $entry_model->id;
			$data_model->save();
			$data_model->reset();
		}
	}

	private function save_quaternion($entry, $key, $entry_model, $data_model)
	{
		if(empty($entry[$key]))
			return;
		
		foreach($entry[$key] as $data)
		{
			$data_model->key = $data["key"];
			$data_model->x = $data["x"];
			$data_model->y = $data["y"];
			$data_model->z = $data["z"];
			$data_model->w = $data["w"];
			$data_model->entry_id = $entry_model->id;
			$data_model->save();
			$data_model->reset();
		}
	}

	public function batch_entries($f3, $params)
	{
		try
		{
			$post = $f3->get("POST");
			$model = new \Models\Entry($this->db);
			$this->db->begin();
			foreach($post["entries"] as $entry)
			{
				$model->scene_id = $entry["scene_id"];
				$model->loggable_id = $entry["loggable_id"];
				$model->event = $entry["event"];
				$model->time = $entry["time"];
				$model->save();

				$data_model = new \Models\StringData($this->db);
				$this->save_data($entry, "strings", $model, $data_model);

				$data_model = new \Models\IntData($this->db);
				$this->save_data($entry, "ints", $model, $data_model);
				
				$data_model = new \Models\FloatData($this->db);
				$this->save_data($entry, "floats", $model, $data_model);

				$data_model = new \Models\Vector3($this->db);
				$this->save_vector3($entry, "vector3s", $model, $data_model);

				$data_model = new \Models\Quaternion($this->db);
				$this->save_quaternion($entry, "quaternions", $model, $data_model);

				$model->reset();
			}
			$this->db->commit();
			echo \Utils::json_encode(array(
				array("success" => 1)
			));
		}
		catch(\Exception $e)
		{
			echo \Utils::json_encode(
				array("message" => $e->getMessage(),
					  "post"    => $post)

			);
		}

		
	}

	public function register_loggable($f3, $params)
	{	
		try
		{
			$post = $f3->get("POST");
			$model = new \Models\Loggable($this->db);
			$model->copyFrom("POST");
			$model->save();
			echo \Utils::json_encode($model->cast());
		}
		catch (\Exception $e)
		{
			echo \Utils::json_encode(
				array("message" => $e->getMessage(),
					  "post"    => $post)
			);
		}
	}

	public function register_scene($f3, $params)
	{
		$post = $f3->get("POST");
			$model = new \Models\Scene($this->db);
		$model->session_id = $post["session_id"];
		$model->name = $post["name"];
		$model->time = $post["time"];
		$model->save();
		echo \Utils::json_encode($model->cast());
	}

	public function close_scene($f3, $params)
	{	
		$id = $f3->get("POST[id]");
		$this->db->exec("
			UPDATE scenes 
			SET end=now()
			WHERE id=?
		", $id);

		echo \Utils::json_encode(array(
			array("message" => "scene " . $id . " closed")
		));
	}


	/**
	 * Deprecated!
	 * Logs an object
	 * @param  [type] $f3     [description]
	 * @param  [type] $params [description]
	 * @return [void]         [description]
	 */
	public function log_object($f3, $params){
		$post = $f3->get("POST");
		$session_id = $post["session_id"];

		
		$gameobject_id = (isset($post["gameobject_id"])) ? 
			$post["gameobject_id"] : "";

		$instance_id = (isset($post["instance_id"])) ? 
			$post["instance_id"] : "";

		// Check for session_id errors
		if(empty($session_id)){
			echo "no session_id";
			return;
		}
		$session = new \Models\Session($this->db);
		$session->load(array("id=?", $session_id));
		if($session->dry()){
			echo "session_id not found";
			return;
		}

		// Load gameobject based on id or instance_id
		$gameobject;

		if(empty($gameobject_id) == false){
			$gameobject = new \Models\GameObject($this->db);
			$gameobject->load(array("id=?", $gameobject_id));

			if($gameobject->dry()){
				echo "gameobject_id not found";
				return;
			}

			if($gameobject->session_id != $session_id){
				echo "gameobject and session mismatch";
				return;
			}	
		}
		else if(empty($instance_id) == false){
			$gameobject = new \Models\GameObject($this->db);
			$gameobject->load(
				array("instance_id=? AND session_id=?",
					  $instance_id,
					  $session_id)
				);
			if($gameobject->dry()){
				echo "gameobject not found";
				return;
			}
			if($gameobject->session_id != $session_id){
				echo "gameobject and session mismatch";
				return;
			}
		}
		else {
			echo "no gameobject_id or instance_id given";
			return;
		}
		

		// Actually do stuff to the db
		$this->db->begin();

		$entry = new \Models\Entry($this->db);
		$entry->session_id = $session_id;
		$entry->event = $post["event"];
		$entry->save();

		$position = new \Models\Vector3($this->db);
		$position->copyFrom("POST[position]");
		$position->gameobject_id = $gameobject->id;
		$position->entry_id = $entry->id;
		$position->save();

		$rotation = new \Models\Quaternion($this->db);
		$rotation->copyFrom("POST[rotation]");
		$rotation->gameobject_id = $gameobject->id;
		$rotation->entry_id = $entry->id;
		$rotation->save();

		$this->db->commit();

		echo \Utils::json_encode(array("success" => 1));

	}

	public function test($f3, $params){
		echo $f3->get("POST[name]");
	}

	public function stop_session($f3, $params){
		$id = $f3->get("POST[id]");
		$this->db->exec("
			UPDATE sessions
			SET end=now()
			WHERE id=?"
			, $id);

		$model = $this->db->exec("
			SELECT * FROM sessions WHERE id=?", $id
			);
		echo \Utils::json_encode($model);
	}



}