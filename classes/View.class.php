<?php
class View extends WBHObject
{

	public $title = 'will hines practices';
	public $header = 'header';
	public $footer = 'footer';
	public $snippetDir = 'views';
	public $body;
	public $data;
		
	public function renderBody($body = null) {
		if ($body) {
			$this->body = $body;
		}		
		$data = $this->getData();		
		include $this->getPageStr($this->header);
		echo $this->body;
		include $this->getPageStr($this->footer);
	}

	private function getPageStr($pagename) {
		return $this->snippetDir.'/'.$pagename.'.php';
	}
	
	public function getData() {
		$this->data['error'] = $this->error;
		$this->data['message'] = $this->message;
		$this->data['sc'] = $_SERVER['SCRIPT_NAME'];
	    return $this->data;
	}	


}