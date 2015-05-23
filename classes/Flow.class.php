<?php
	
class Flow extends WBHOBject {
	
	public $whiteList;
	public $params;
	
	function __construct($vars = null) {
		$this->setWhiteList($vars);
	}
		
	public function setWhiteList($whiteList) {
		$this->whiteList = $whiteList;
		foreach ($this->whiteList as $va) {
			$this->get($va);
		}
	}

	public function get($va) {
		$this->params[$va] = isset($_REQUEST[$va]) ? $_REQUEST[$va] : '';
	}


}	
	
	
?>