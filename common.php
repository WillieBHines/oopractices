<?php
session_start();
	
function my_autoloader($class) {
    include 'classes/' . $class . '.class.php';
}
spl_autoload_register('my_autoloader');

$view = new View(); // will use on every page	
$flow = new Flow(array('ac', 'v', 'wid', 'uid', 'key')); // deals with parameters, urls


$statuses = new Statuses();
define('ENROLLED', $statuses->find_status_id('enrolled'));
define('WAITING', $statuses->find_status_id('waiting'));
define('DROPPED', $statuses->find_status_id('dropped'));
define('INVITED', $statuses->find_status_id('invited'));
define('LATE_HOURS', 12);
define('DEBUG_MODE', false);

$wk = new Workshop($flow->params['wid']);
//check waiting list if there is a workshop
$u = new User($flow->params['uid']);
	
?>