<?php
include 'common.php';	

if ($wk->getCol('id')) { // if workshop data is set, default to viewing it
	$flow->params['v'] = 'view';
}

switch ($flow->params['ac']) {
	
	case 'lo':
		$u->log_out();
		$v->setFeedback($u->message, $u->error);
		break;
	
	case 'textup':
		$u->save( $u->get_text_preferences_form()->get_values() );
		$v->setFeedback($u->message, $u->error);
		$flow->params['v'] = 'text';
		break;
		
	case 'reset':
		$u->reset_current_key();
		$v->set_feedback($u->message, $u->error);
		break;

	case 'link':
		$params = $u->get_login_form()->get_values();
		$u->send_login_email($params['email']);  // this will make a new user if we need it
		$v->set_feedback($u->message, $u->error);
		break;	
		
	case 'cemail':
		$u->change_email_request();
		$v->set_feedback($u->message, $u->error);
		$flow->params['v'] = 'email';
		break;
		
	case 'concemail':
		$u->change_email();
		$v->set_feedback($u->message, $u->error);
		break;
	
	case 'enroll':
		$r->set_registration($wk, $u);
		$r->change_status(ENROLLED, true);
		$v->set_feedback($r->message, $r->error);
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
		$data['upcoming'] = $wk->get_workshops_list();
		$data['transcript'] = $u->get_transcript();
		$template = 'home';	
	
}

$data['admin'] = false;
$data['workshop'] = $wk;
$data['user'] = $u;
$data['registration'] = $r;
$v->renderPage($template, $data);

?>

