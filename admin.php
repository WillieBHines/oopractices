<?php
include 'common.php';	
$security = new Security();

$data['admin'] = true;

if (!$security->is_validated()) {
	$v->renderHTML($security->validate_user_form(), $data);
	exit;
}	

// action 
switch ($flow->params['ac']) {
	case 'edit':
		$wk->save( $wk->get_workshop_form()->get_values() );
		$v->set_feedback($wk->message, $wk->error);
		$flow->params['v'] = 'view';

	break;
	
	
}
	
// view
switch ($flow->params['v']) {	
	case 'view':
		$data['students'] = $r->get_all_students($wk);
		$st = new StatusChangeLog();
		$data['changes'] = $st->get_status_change_log($wk);
		$template = 'workshop_edit';
		break;
		
	default:
		$wlist = new Workshop();
		$data['workshop_list'] = $wlist->get_workshops_list(true);
		$template = 'home_admin';	
	
}

$data['wk'] = $wk;
$data['u'] = $u;
$data['r'] = $r;
$data['sc'] = $_SERVER['SCRIPT_NAME'];
$v->renderPage($template, $data);


?>