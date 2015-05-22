<?php
/*
* these routines are sort of logically part of 
* registration management and user management
* but it felt neater to put them together since they
* deal with email and texting
*/
class Messages extends WBHObject {

	public $workshop;
	public $user;
	public $registration;
	public $carriers;


	function __construct() {
		
		// get global carriers array and put it locally for convenience
		$carriers = new Carriers;
		$this->carriers = $carriers->get_carriers();
	}
	
	

}	
?>