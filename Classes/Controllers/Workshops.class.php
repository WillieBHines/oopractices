<?php
namespace Classes\Controllers;

class Workshops extends \Classes\Controller {
	
	public $wk; //workshop model
	
	public function __construct() {
		parent::__construct(); // sets user, view, flow
		$this->wk = new \Classes\Models\Workshop();
	
	}
	
	public function index() {
	
		$data = array();
		$data['workshop_list'] = $this->wk->get_workshops_list();
		$data['transcript'] = $this->u->get_transcript();
		$data['wk'] = $this->wk;
		$this->sendToView('home', $data);
	}
	
	
}


