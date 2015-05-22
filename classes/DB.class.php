<?php
/*
* this is either a static class or a singleton (not sure which) 
* so that the connection to the database is essentially global
*/
class DB extends WBHOBject {
	
	private static $db = null; // db link
	
    private function __construct(){
         self::$db = mysqli_connect('localhost', 'whines_workshops', 'meet1962', 'whines_workshops');
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