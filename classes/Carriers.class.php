<?
/*
* most classes get one instance per row
* this class gets the whole table since it's just a lookup table
* stores the data in a static property (essentially global)
*/
class Carriers extends Model {
	
	protected $tableName = 'carriers';
	public static $carriers;
	
	function __construct() {
		parent::__construct(); // sets DB object
		$this->get_carriers();
	}
	
	public function get_carriers() {
		if (!Carriers::$carriers) {
			Carriers::$carriers = $this->get_everything();
		}
		return Carriers::$carriers;
	}
	
	public function get_carriers_dropdown() {
		$c = $this->get_carriers();
		$drop = array();
		foreach ($c as $id => $row) {
			$drop[$id] = $row['network'];
		}
		return $drop;
	}
				
}	
?>