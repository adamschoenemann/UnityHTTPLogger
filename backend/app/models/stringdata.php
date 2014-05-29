<?php
namespace Models;
class StringData extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "string_data");
	}
	
}