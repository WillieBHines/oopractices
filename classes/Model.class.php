<?php 
/*
* almost every class corresponds to a table
* this class makes sure they all have basic database methods (query, getting an error, escaping data)
* and then a few common methods to manipulate data sets
*/
class Model extends WBHObject {

	protected $db; // db connection	
	public $cols; // an array of columns for the row/object: id, name, timestamp, etc
	protected $tableName = null;
	protected $valueCol = null;
	protected $nameCol = null;


	function __construct() {
		$this->db = DB::init();
	}

	/*
	* data methods
	*/
	public function get_everything() {
		$sql = "select * from {$this->tableName} order by id";
		$rows = $this->query($sql) or $this->db_error();
		$all = array();
		while ($row = mysqli_fetch_assoc($rows)) {
			$all[$row['id']] = $row;
		}
		return $all;
	}		
		
	// a generic "get a row" method, good for lookup tables that have more fields than just id and name
	public function get_row($id) {
		$sql = "select * from {$this->tableName} where id = ".$this->mres($id)." order by id";
		$rows = $this->query($sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			return $row;
		}
		return false;
	}	
		
	public function get_dropdown_array() {
		$sql = "select {$this->nameCol}, {$this->valueCol} from {$this->tableName} order by {$this->valueCol}";
		$rows = $this->query($sql) or $this->db_error();
		$dropdown = array();
		while ($row = mysqli_fetch_assoc($rows)) {
			$dropdown[$row[$this->valueCol]] = $row[$this->nameCol];
		}
		return $dropdown;
	}


	public function getCol($id) {
		if (isset($this->cols[$id])) {
			return $this->cols[$id];
		} else {
			return null;
		}
	}

	/*
	* data connection methods
 	*/
	public function query($sql) {
 		$rows = mysqli_query($this->db, $sql) or $this->db_error();	
 		return $rows;
 	}

	public function mres($thing) {
		return mysqli_real_escape_string($this->db, $thing);
	}
	
	public function db_error() {
		if (mysqli_error ($this->db )) {
			echo mysqli_error($this->db);
			die;
		}
		return true;
	}

	private function mysqli_field_name($result, $field_offset)
	{
	    $properties = mysqli_fetch_field_direct($result, $field_offset);
	    return is_object($properties) ? $properties->name : null;
	}
		
}



?>