<?php
namespace API;
class Log extends API {

	public function register_session($f3, $params){
		$post = $f3->get("POST");

		if(empty($post["app_version"])){
			echo "no app version specified";
			return;
		}

		$this->db->begin();

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

	public function entries($f3, $params)
	{
		try
		{
			$post = $f3->get("POST");
			$model = new \DB\SQL\Mapper($this->db, "entries");
			$this->db->begin();
			foreach($post["entries"] as $entry)
			{
				$model->scene_id = $entry["scene_id"];
				$model->loggable_id = $entry["loggable_id"];
				$model->event = $entry["event"];
				$model->time = $entry["time"];
				$model->save();

				$data_model = new \DB\SQL\Mapper($this->db, "string_data");
				$this->save_data($entry, "strings", $model, $data_model);

				$data_model = new \DB\SQL\Mapper($this->db, "int_data");
				$this->save_data($entry, "ints", $model, $data_model);
				
				$data_model = new \DB\SQL\Mapper($this->db, "float_data");
				$this->save_data($entry, "floats", $model, $data_model);

				$data_model = new \DB\SQL\Mapper($this->db, "vector3");
				$this->save_vector3($entry, "vector3s", $model, $data_model);

				$data_model = new \DB\SQL\Mapper($this->db, "quaternion");
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
			$model = new \DB\SQL\Mapper($this->db, "loggable");
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
		echo "hey";
		try
		{
			$post = $f3->get("POST");
				$model = new \DB\SQL\Mapper($this->db, "scenes");
			$model->session_id = $post["session_id"];
			$model->name = $post["name"];
			$model->time = $post["time"];
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



	public function close_session($f3, $params){
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