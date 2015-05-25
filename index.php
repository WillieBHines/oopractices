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
	
	case 'textup':
		$u->save( $u->get_text_preferences_form()->get_values() );
		$flow->params['v'] = 'text';
		break;
		
	case 'reset':
		if ($u->logged_in()) {
			$u->reset_current_key();
			$message = 'I made a new key, sent it to you in an email and then logged you out.';
		}
		break;

	case 'link':
		$params = $u->get_login_form()->get_values();
		if ($u->send_login_email($params['email'])) { // this will make a new user if we need it
			$message = "Thanks! I've sent a link to your {$u->getCol('email')}. Click it! (check your spam folder).";
 		} else {
			$error = $u->error;
		}
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
		
	case 'email':
		$template = 'change_email';
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

