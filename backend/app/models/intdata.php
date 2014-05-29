<?php
namespace Models;
class IntData extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "int_data");
	}
	
}