<?php
namespace Classes\Controllers;

class Workshops extends \Classes\Controller {
	
	public $wk; //workshop model
	protected $r; // global registration
	
	
	public function __construct() {
		parent::__construct(); // sets user, view, flow
		$this->wk = new \Classes\Models\Workshop();
		$this->r = new \Classes\Models\Registration();
	}
	
	public function index() {
	
		$data = array();
		$data['workshop_list'] = $this->wk->get_workshops_list();
		$data['transcript'] = $this->u->get_transcript();
		$data['wk'] = $this->wk;
		$data['r'] = $this->r;
		$this->sendToView('home', $data);
	}
	
	
}


