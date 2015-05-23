<?php
session_start(); // store key in $_SESSION
	
// autoload classes
function my_autoloader($class) {
    include 'classes/' . $class . '.class.php';
}
spl_autoload_register('my_autoloader');

// constants
$statuses = new Statuses();
define('ENROLLED', $statuses->find_status_id('enrolled'));
define('WAITING', $statuses->find_status_id('waiting'));
define('DROPPED', $statuses->find_status_id('dropped'));
define('INVITED', $statuses->find_status_id('invited'));
define('LATE_HOURS', 12);
define('DEBUG_MODE', false);

// page content and flow
$view = new View(); // will use on every page	
$flow = new Flow(array('ac', 'v', 'wid', 'uid')); // deals with parameters, urls
$template = ''; // what template to load
$data = ''; // data for the template

// get three main objects going
$wk = new Workshop($flow->params['wid']);
$u = new User($flow->params['uid']);
$r = new Registration;

?>