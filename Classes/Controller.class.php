<?php 
namespace Classes;
/*
* almost every class corresponds to a table
* this class makes sure they all have basic database methods (query, getting an error, escaping data)
* and then a few common methods to manipulate data sets
*/
class Controller extends WBHObject {

	protected $db; // db connection	

	function __construct() {
		$this->db = DB::init();
	}

}



?>
