<?php
namespace Models;
class Gallery extends SlugModel {

	function __construct(\DB\SQL $db){
		parent::__construct($db, "galleries");
	}

	function is_valid(){
		return (
			!empty($this->title)
		);
	}

}