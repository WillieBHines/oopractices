<?php
session_start(); // store key in $_SESSION



// autoload classes

spl_autoload_register(function ($className) {

    # Usually I would just concatenate directly to $file variable below
    # this is just for easy viewing on Stack Overflow)
        $ds = DIRECTORY_SEPARATOR;
        $dir = __DIR__;

    // replace namespace separator with directory separator (prolly not required)
        $className = str_replace('\\', $ds, $className);

    // get full name of file containing the required class
        $file = "{$dir}{$ds}{$className}.class.php";
		//echo "$file\n";
		//die;

    // get file if it is readable
        if (is_readable($file)) require_once $file;
});


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
$v = new Classes\View(); // used to render templates	
$flow = new Classes\Flow(array('ac', 'wid', 'uid', 'v')); // deals with parameters, urls
$template = ''; // what template to load
$data = ''; // data for the template

// get three main data objects going
$wk = new Classes\Models\Workshop ($flow->params['wid']);
$u = new Classes\Models\User(); 
$r = new Classes\Models\Registration();

?>