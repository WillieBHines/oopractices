<?php
/*
* most classes get one instance per row
* this class gets the whole table since it's just a lookup table
* stores the data in a static property (essentially global)
*/
class Locations extends Model {
	
	protected $table_name = 'locations';
	public static $locations;
	
	function __construct() {
		parent::__construct(); // sets DB object
		$this->get_locations();
	}
	
	public function get_locations() {
		if (!Locations::$locations) {
			Locations::$locations = $this->get_everything();
		}
		return Locations::$locations;
	}
	
	public function get_locations_dropdown() {
		$c = $this->get_locations();
		$drop = array();
		foreach ($c as $id => $row) {
			$drop[$id] = $row['place'];
		}
		return $drop;
	}
					
}	
?>