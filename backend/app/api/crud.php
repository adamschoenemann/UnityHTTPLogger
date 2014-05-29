<?php
namespace API;
abstract class CRUD extends API {

	public function view_all($f3, $params){
		$models = $this->db->exec(
			"SELECT * FROM " . $this->get_table_name()
		);

		echo \Utils::json_encode($models);
	}

	public function view($f3, $params){
		$id = $params["id"];
		$model = $this->db->exec(
			"SELECT * FROM " . $this->get_table_name() . " WHERE id=$id"
		);

		echo \Utils::json_encode($model);
	}

	public function create($f3, $params){
		$model = $this->create_model();
		$model->copyFrom("POST");
		$model->save();
		echo \Utils::json_encode($model->cast());
	}

	protected abstract function get_table_name();
	protected abstract function create_model();

}