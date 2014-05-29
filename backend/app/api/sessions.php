<?php
namespace API;
class Sessions extends CRUD {
	

	protected function get_table_name(){
		return "sessions";
	}

	protected function create_model(){
		return new \Models\Session();
	}

}