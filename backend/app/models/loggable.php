<?php
namespace Models;
class Loggable extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "loggables");
	}
	
	
}