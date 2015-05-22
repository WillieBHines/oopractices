<?php
class Key extends WBHObject {

	protected $db;
	public $keycode = null;
	
	function __construct($keycode = null) {
		if ($keycode) {
			$this->keycode = $_SESSION['s_key'] = $keycode;
		} elseif (isset($_REQUEST['key']) && $_REQUEST['key']) {
			$_SESSION['s_key'] = $this->keycode = $_REQUEST['key'];
		} elseif (isset($_SESSION['s_key']) and $_SESSION['s_key']) {
			$this->keycode = $_SESSION['s_key'];
		}	
	}
	
}
?>