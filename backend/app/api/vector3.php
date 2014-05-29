<?php
namespace API;
class Vector3 extends CRUD {

	protected function get_table_name(){
		return "vector3";
	}

	protected function create_model(){
		return new \Models\Vector3();
	}


}