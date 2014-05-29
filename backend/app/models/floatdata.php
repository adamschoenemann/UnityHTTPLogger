<?php
namespace Models;
class FloatData extends \DB\SQL\Mapper {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "float_data");
	}
	
}