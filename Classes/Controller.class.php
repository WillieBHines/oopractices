<?php 
namespace Classes;
/*
* almost every class corresponds to a table
* this class makes sure they all have basic database methods (query, getting an error, escaping data)
* and then a few common methods to manipulate data sets
*/
class Controller extends WBHObject {

	protected $u; // used to point to the global user
 	protected $v; // used to point to the global view object
	protected $f; // user to point to the global Flow (params) object

	function __construct() {
		$this->u = $GLOBALS['u'];
		$this->v = new View(); // used to render templates	
		$this->f = $GLOBALS['f'];
	}

	function sendToView($template, $data) {
		if (!isset($data['admin'])) { $data['admin'] = false; }
		if (!isset($data['u'])) { $data['u'] = $this->u; }
		if (!isset($data['sc'])) { $data['sc'] = $_SERVER['SCRIPT_NAME']; }
		$this->v->renderPage($template, $data);
	}

}



?>
