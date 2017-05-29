<?php
session_start(); // store key in $_SESSION
// autoload classes

function my_autoloader($class) {
    include 'classes/' . $class . '.class.php';
	
    // Cut Root-Namespace
     $class = str_replace( __NAMESPACE__.'\\', '', $class );
     // Correct DIRECTORY_SEPARATOR
     $class = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, __DIR__.DIRECTORY_SEPARATOR.$class.'.class.php' );
     // Get file real path
     if( false === ( $class = realpath( $class ) ) ) {
         // File not found
         return false;
     } else {
         require_once( $class );
         return true;
     }	
}
spl_autoload_register('my_autoloader');


//spl_autoload_extensions(".class.php"); // comma-separated list
//spl_autoload_register();

// constants
$statuses = new Classes\Models\Statuses();
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
$r = new Registration();

?>