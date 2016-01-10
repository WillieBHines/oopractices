<?php
class StatusChangeLog extends Model {
	
	private $workshop;
	private $user;
	
	function __construct(Workshop $wk = null, User $u = null) {
		parent::__construct(); // sets DB object
		if ($wk) {
			$this->workshop = $wk;
		}
		if ($u) {
			$this->user = $u;
		}
	}

	/*
	* change log stuff
	*/
	function update_change_log($status_id) {
		$sql = sprintf("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (%u, %u, %u, '%s')",
		$this->mres ($this->workshop->cols['id']),
		$this->mres ($this->user->cols['id']),
		$this->mres ($status_id),
		date('Y-m-d H:i:s', time()));
		$this->query($sql) or $this->db_error();
		return $this;
	}

	function get_status_change_log(Workshop $wk = null) {

		$sql = "select s.*, u.email, st.status_name, wk.title, wk.start, wk.end from status_change_log s, users u, statuses st, workshops wk where";
		if ($wk) { 
			$sql .= " workshop_id = ".$this->mres($wk->getCol('id'))." and "; 
		}
		$sql .= " s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";

		$rows = $this->query($sql) or $this->db_error();

		$changes = array();
		while ($row = mysqli_fetch_assoc($rows)) {
			$tempwk = new Workshop(); // empty workshop object, use this ($tempwk) for data
			$tempwk->cols = $row; // this will include workshop columns and extra columns
			$tempwk->format_workshop_startend(); // this will format the columns we need without calling db
			$changes[$row['id']]  = array_merge($tempwk->cols, $row); // should have status change log info plus formatted workshop info
		}
		return $changes;
			
	}	

	public function change_user_id($old_id, $new_id) {
		$sql = "update status_change_log set user_id = ".$this->mres($new_id)." where user_id = ".$this->mres($old_id);
		return $this->query($sql) or $this->db_error();
		
	}

}	
?>