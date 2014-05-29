<?php
namespace API;
class Entries extends CRUD {

	protected function get_table_name(){
		return "entries";
	}

	protected function create_model(){
		return new \Models\Entry();
	}


}