<?php
include 'common.php';	
$security = new Security();

if (!$security->is_validated()) {
	$v->renderHTML($security->validate_user_form());
	exit;
}	
	
	
// view
switch ($flow->params['v']) {	
	default:
		$data['workshop_list'] = $wk->get_workshops_list(true);
		$template = 'home_admin';	
	
}

$data['admin'] = true;
$data['wk'] = $wk;
$data['u'] = $u;
$data['r'] = $r;
$data['sc'] = $_SERVER['SCRIPT_NAME'];
$v->renderPage($template, $data);


?>