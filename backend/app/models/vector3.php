<?php
namespace Models;
class Vector3 extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "vector3");
	}
	
	
}