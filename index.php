<?php
include 'common.php';	

// might be able to pull some of these out into a Form object
$flow->whiteList = array('ac', 'wid', 'uid', 'email', 'v', 'key', 'message', 'phone', 'carrier_id', 'send_text', 'newemail');
	
$body = 'hello';	



$view->renderBody($body);

?>

