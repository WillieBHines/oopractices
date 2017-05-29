<?php
namespace Classes;
class View extends WBHObject
{

	public $title = 'will hines practices';
	public $data = array();
	private $header = 'header';
	private $footer = 'footer';
	private $snippetDir = 'views';

	public function renderPage($page = null, $data = null) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
		//include $this->getPageStr($this->common_vars_page);
		include $this->getPageStr($this->header);
		include $this->getPageStr($page);
		include $this->getPageStr($this->footer);
	}

			
	public function renderSnippet($bargle, $data=null) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
	    ob_start();
		include $this->getPageStr($bargle);
	    return ob_get_clean();
	}
	
	public function renderHTML($html, $data = null) {
		$data = $this->updateData($data); // add some things to it
	    if (is_array($data) && !empty($data)) {
	        extract($data);
	    }
		//include $this->getPageStr($this->common_vars_page);
		include $this->getPageStr($this->header);
		echo $html;
		include $this->getPageStr($this->footer);
	}

	private function getPageStr($pagename) {
		return $this->snippetDir.'/'.$pagename.'.php';
	}
	
	// just add to data
	public function updateData($data = null) {
		if ($data) {
			$this->data = array_merge($this->data, $data); // take the data that's passed in
		}
		$this->data['sc'] = $_SERVER['SCRIPT_NAME'];
		$this->data['path'] = $this->snippetDir.'/';
	    return $this->data;
	}	

	public function set_feedback($message = null, $error = null) {
		if ($message) {
			$this->data['message'] = $message;	
		}
		if ($error) {
			$this->data['error'] = $error;
		}
	}

}