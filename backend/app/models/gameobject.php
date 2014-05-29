<?php
namespace Models;
class GameObject extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "gameobject");
	}
	
	
}