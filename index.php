<?php
include 'common.php';	

// might be able to pull some of these out into a Form object
$flow->whiteList = array('ac', 'wid', 'uid', 'v');

if ($flow->params['wid']) {
	$flow->params['v'] = 'view';
}
	
// view
switch ($flow->params['v']) {
	
	case 'view':
		$r->set_registration($wk, $u);
		$template = 'view_workshop';
		break;
	
	
	default:
		$template = 'home';	
	
}

$data['workshop'] = $wk;
$data['user'] = $u;
$data['registration'] = $r;
$view->renderPage($template, $data);

?>

