<?php
session_start(); // store key in $_SESSION

// set function for autoloading classes
spl_autoload_register(function ($className) {
        $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $file = __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."{$className}.class.php";
		//echo "$file\n";
		//die;
        if (is_readable($file)) require_once $file;
});

// set constants
$statuses = new Classes\Models\Statuses();
define('ENROLLED', $statuses->find_status_id('enrolled'));
define('WAITING', $statuses->find_status_id('waiting'));
define('DROPPED', $statuses->find_status_id('dropped'));
define('INVITED', $statuses->find_status_id('invited'));
define('LATE_HOURS', 12);
define('DEBUG_MODE', false);
define('URL', "http://{$_SERVER['HTTP_HOST']}/oopractices/");
define('ASSETS', "/oopractices/assets/"); // css, js files, images
define('WEBMASTER', "will@willhines.net");

// global objects
// i know it's bad
// but i can't think of a simple alternative
$v = new Classes\View(); // used to render templates	
$f = new Classes\Flow(array('ac', 'wid', 'uid', 'v')); // deals with parameters, urls
$u = new Classes\Models\User();  // set up the user. this checks session, params, etc.
$r = new Classes\Models\Registration();

/*
$template = ''; // what template to load
$data = ''; // data for the template

// get three main data objects going
$wk = new Classes\Models\Workshop ($f->params['wid']);
$u = new Classes\Models\User(); 
*/
?>