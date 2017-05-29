<?
/*
* most classes get one instance per row
* this class gets the whole table since it's just a lookup table
* stores the data in a static property (essentially global)
*/
class Statuses extends Model {
	
	protected $table_name = 'statuses';
	protected $valueCol = 'id';
	protected $nameCol = 'status_name';
	public static $statuses;

	
	function __construct() {
		parent::__construct(); // sets DB object
		$this->get_statuses();
	}
	
	public function get_statuses() {
		if (!Statuses::$statuses) {
			Statuses::$statuses = $this->get_dropdown_array();
		}
		return Statuses::$statuses;
	}
	
	public function find_status_id($status_name) {
		foreach (Statuses::$statuses as $val => $sname) {
			if ($status_name == $sname) {
				return $val;
			}
		}
		return false;
	}
			
}	
?>