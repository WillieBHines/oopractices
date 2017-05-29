<?php
namespace Classes;

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
		} elseif (isset($_COOKIE['c_key'])) {
			$this->keycode = $_SESSION['s_key'] = $_COOKIE['c_key'];
		}
		
		if ($this->keycode && !headers_sent()) {
			setcookie("c_key", $this->keycode, time()+(3600*24*7));  /* expire in 1 week */
		}
		
	}
	
	public function make_new_keycode() {
		return substr(md5(uniqid(mt_rand(), true)), 0, 16);
	}
	
	function __destruct() {
		unset($_SESSION['s_key']);
	}
	
}
?>