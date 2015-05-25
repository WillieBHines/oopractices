<?php
include 'common.php';	

// might be able to pull some of these out into a Form object
$flow->whiteList = array('ac', 'wid', 'uid', 'v');

if ($flow->params['wid']) {
	$flow->params['v'] = 'view';
}

switch ($flow->params['ac']) {
	
	case 'lo':
		$u->log_out();
		$message = 'You are logged out!';
		break;
	
}	
// view
switch ($flow->params['v']) {
	
	case 'view':
		$r->set_registration($wk, $u);
		$template = 'view_workshop';
		break;
		
		case 'text':
		$template = 'text_preferences';
		
		break;
	
	case 'faq':
		$template = 'faq';
		break;
	
	default:
		$data['upcoming'] = $wk->get_workshops_list(0);
		$data['transcript'] = $u->get_transcript();
		$template = 'home';	
	
}

$data['admin'] = false;
$data['workshop'] = $wk;
$data['user'] = $u;
$data['registration'] = $r;
$data['message'] = isset($message) ? $message : null;
$data['error'] = isset($error) ? $error : null;
$view->renderPage($template, $data);

?>

