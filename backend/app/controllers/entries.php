<?php

namespace Controllers;

class Entries extends Controller
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
		$models = $model->find();
		$f3->set("models", $models);
		$f3->set("table_headers", array_keys($models[0]->cast()));
		echo \Template::instance()->render("sessions.html");
	}

	function view($f3, $params)
	{
		$id = $params["id"];
		$entry = new \Models\Entry($this->db);
		$entry->load(array("id=?", $id));
		$entry->load_metadata();
		// echo \Utils::json_encode($entry->cast(), true);
		echo "<pre>";
		echo \Utils::json_encode($entry->cast(), true);
		echo "</pre>";
	}

	private function get_data($tabel, $entry_id)
	{
		return "SELECT * FROM $tabel WHERE entry_id=$entry_id";
	}

}