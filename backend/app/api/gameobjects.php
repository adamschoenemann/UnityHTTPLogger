<?php
namespace API;
class GameObjects extends CRUD {

	protected function get_table_name(){
		return "gameobject";
	}

	protected function create_model(){
		return new \Models\GameObject();
	}


}