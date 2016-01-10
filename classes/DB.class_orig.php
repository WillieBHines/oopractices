<?php
/*
* this is either a static class or a singleton (not sure which) 
* so that the connection to the database is essentially global
*/
class DB extends WBHOBject {
	
	private static $db = null; // db link
	
    private function __construct(){
		// set up next line
        // self::$db = mysqli_connect(servername, username, password, databasename);
     }

     public static function init() {
         if (self::$db === null) {
             $dummydb =  new self(); // recreate connection
         }
         return self::$db;
     }	
	 
 	private function __clone() { }	 
}	
	
	
	
?>