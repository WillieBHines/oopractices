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
		if (!$status_id) {
			$this->setError("no status id set");
			return $this;
		}
		
		$sql = sprintf("insert into status_change_log (workshop_id, user_id, status_id, happened) VALUES (%u, %u, %u, '%s')",
		$this->mres ($this->workshop->cols['id']),
		$this->mres ($this->user->cols['id']),
		$this->mres ($status_id),
		date('Y-m-d H:i:s', time()));
		$this->query($sql) or $this->db_error();
		return $this;
	}

	function get_status_change_log(Workshop $wk = null) {

		$sc = $_SERVER['SCRIPT_NAME'];
		
		$sql = "select s.*, u.email, st.status_name, wk.title, wk.start, wk.end from status_change_log s, users u, statuses st, workshops wk where";
		if ($wk) { 
			$sql .= " workshop_id = ".$this->mres($wk->cols['id'])." and "; 
		}
		$sql .= " s.workshop_id = wk.id and s.user_id = u.id and s.status_id = st.id order by happened desc";

		$rows = $this->query($sql) or $this->db_error();

		$log = '';
		$wkname = '';
		while ($row = mysqli_fetch_assoc($rows)) {
			
			if (!$wk) {
				$tempwk = new Workshop(); // empty workshop object, use this ($tempwk) for data
				$tempwk->cols = $row; // this will include workshop columns and extra columns
				$tempwk->format_workshop_startend(); // this will format the columns we need
				$wkname = "<a href='$sc?v=ed&wid={$tempwk->cols['workshop_id']}'>{$tempwk->cols['title']}</a><br><small>{$tempwk->cols['showstart']}</small></td><td>";
			}
			
			// user query ($row) for data
			$log .= "<tr><td>{$row['email']}</td><td>$wkname{$row['status_name']}</td><td><small>".date('j-M-y g:ia', strtotime($row['happened']))."</small></td></tr>\n";
			
		}
		if (!$log) {
			$log = 'No recorded updates.';
		} else {
			$log = "<table class='table'>$log</table>\n";
		}
		return $log;
	}	

}	
?>