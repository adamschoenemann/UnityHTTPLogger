<?php
namespace Models;
class Quaternion extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "quaternion");
	}
	
	
}