<?php
namespace Classes;	
class Flow extends WBHOBject {
	
	public $whiteList;
	public $params;
	
	function __construct($vars = null) {
		if ($vars) {
			$this->setWhiteList($vars);
		}
	}
		
	public function setWhiteList($whiteList) {
		if (is_array($whiteList)) {
			$this->whiteList = $whiteList;
			foreach ($this->whiteList as $va) {
				$this->get($va);
			}
		}
	}

	public function get($va) {
		$this->params[$va] = isset($_REQUEST[$va]) ? $_REQUEST[$va] : '';
	}


}	
	
	
?>