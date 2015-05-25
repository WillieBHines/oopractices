<?php
class View extends WBHObject
{

	public $title = 'will hines practices';
	public $data;
	private $header = 'header';
	private $footer = 'footer';
	private $snippetDir = 'views';
	private $common_vars_page = 'common_variables'; // declaring common variables that most templates will need

	public function renderPage($page = null, $data = null) {
		$this->data = $data; // take the data that's passed in
		$data = $this->updateData(); // add some things to it
		include $this->getPageStr($this->common_vars_page);
		include $this->getPageStr($this->header);
		include $this->getPageStr($page);
		include $this->getPageStr($this->footer);
	}

	private function getPageStr($pagename) {
		return $this->snippetDir.'/'.$pagename.'.php';
	}
	
	// just add to data
	public function updateData() {
		$this->data['sc'] = $_SERVER['SCRIPT_NAME'];
		$this->data['path'] = $this->snippetDir.'/';
	    return $this->data;
	}	


}