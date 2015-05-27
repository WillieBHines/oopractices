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
define('URL', "http://{$_SERVER['HTTP_HOST']}/oopractices/");
define('WEBMASTER', "will@willhines.net");



// page content and flow
$v = new View(); // used to render templates	
$flow = new Flow(array('ac', 'wid', 'uid', 'v')); // deals with parameters, urls
$template = ''; // what template to load
$data = ''; // data for the template

// get three main data objects going
$wk = new Workshop ($flow->params['wid']);
$u = new User(); 
$r = new Registration;

?>