<?php 
/*
* almost every class corresponds to a table
* this class makes sure they all have basic database methods (query, getting an error, escaping data)
* and then a few common methods to manipulate data sets
*/
class Model extends WBHObject {

	protected $db; // db connection	
	public $cols; // an array of columns for the row/object: id, name, timestamp, etc
	protected $table_name = null;
	protected $valueCol = null;
	protected $nameCol = null;


	function __construct() {
		$this->db = DB::init();
	}

	/*
	* data methods
	*/
	public function get_everything() {
		$sql = "select * from {$this->table_name} order by id";
		$rows = $this->query($sql) or $this->db_error();
		$all = array();
		while ($row = mysqli_fetch_assoc($rows)) {
			$all[$row['id']] = $row;
		}
		return $all;
	}		
		
	// a generic "get a row" method, good for lookup tables that have more fields than just id and name
	public function get_row($id) {
		$sql = "select * from {$this->table_name} where id = ".$this->mres($id)." order by id";
		$rows = $this->query($sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			return $row;
		}
		return false;
	}	
		
	public function get_dropdown_array() {
		$sql = "select {$this->nameCol}, {$this->valueCol} from {$this->table_name} order by {$this->valueCol}";
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
	
	public function save($params) {
		if (isset($params['id'])) {
			$fields = $this->get_field_names();
			$sql = '';
			foreach ($fields as $f) {
				if (isset($params[$f]) && $f != 'id') {
					if ($sql) { $sql .= ', '; }
					$sql .= " $f = '".$this->mres($params[$f])."'";
				}
			}
			$sql = "update {$this->table_name} set $sql where id = ".$this->mres($params['id']);
			$this->query($sql) or $this->db_error();
			$this->merge_into_cols($params); // update object with data
		} else {
			return false;
		}
		
	}
	
	private function get_field_names() {
		$sql = "select * from {$this->table_name} limit 1";
		$rows = $this->query($sql) or $this->db_error();
		$finfo = mysqli_fetch_fields($rows);
		$fields = array();
		foreach ($finfo as $val) {
			$fields[] = $val->name;
		}
		return $fields;
	}
	
	private function merge_into_cols($params) {
		$this->cols = array_merge($this->cols, $params);
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