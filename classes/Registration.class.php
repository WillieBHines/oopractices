<?
class Registration extends Model {
	
	private $statuses;
	private $workshop;
	private $user;
	protected $table_name = 'registrations';
		
	function __construct(Workshop $wk = null, User $u = null) {
		parent::__construct(); // sets DB object
		$st = new Statuses();
		$this->statuses = $st->get_statuses();
		if ($wk && $u) {
			$this->set_registration($wk, $u);
		}
	}
	
	public function set_registration(Workshop $wk, User $u) {
		
		$this->workshop = $wk;
		$this->user = $u;
		
		if ($wk->cols['id'] && $u->cols['id']) { // if we have IDs, then they are set
			// get a registration
			$sql = "select r.* from registrations r where r.workshop_id = ".$this->mres($wk->cols['id'])." and user_id = ".$this->mres($u->cols['id']);
			$rows = $this->query( $sql) or $this->db_error($sql);
			while ($row = mysqli_fetch_assoc($rows)) {
		
				$row['status_name'] = $this->statuses[$row['status_id']]; // saves us looking it up in statuses variable
				$this->cols = $row; // set values into model
				$this->set_rank(); // set rank for this user, status_id in this workshop
				return $this; // loaded with registration data
			}
		}
		return $this; // no registration found
	}

	public function set_rank($cols = null) {
		if ($cols) {
			$this->cols = $cols;
		}
		// figure rank in workshop
		$sql2 = "select r.* from registrations r where r.workshop_id = ".$this->mres($this->cols['workshop_id'])." and r.status_id = '".$this->mres($this->cols['status_id'])."' order by last_modified desc";
		$rows2 = $this->query( $sql2) or $this->db_error();
		$i = 1;
		while ($row2 = mysqli_fetch_assoc($rows2)) {
			if ($row2['id'] == $this->cols['id']) {
				break;
			}
			$i++;
		}
		$this->cols['rank'] = $i;
		
	}

	/*
	* methods for dealing with a workshop's set of registrations
	*/
	
	// get registration totals for a particular workshop and status
	public function get_registration_totals(Workshop $wk, $status_id = ENROLLED) {
		$sql = "select count(*) as total from registrations where workshop_id = ".$this->mres($wk->cols['id'])." and status_id = '".$this->mres($status_id)."'";
		$rows = $this->query($sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			return $row['total'];
		}
		return 0;
	}
	
	public function registered_while_sold_out(Workshop $wk) {
		$sql = "update registrations set while_soldout = 1 where workshop_id = ".$this->mres($wk->cols['id'])." and status_id = '".ENROLLED."'";
		return $this->query( $sql) or $this->db_error();
	}
	
	public function invite_next_waiting(Workshop $wk) {
		// invite next person
		$sql = "select * from registrations where workshop_id = ".mres($wk->cols['id'])." and status_id = '".WAITING."' order by last_modified desc limit 1";
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->set_registration($wk, new User($row['user_id']));
			$this->change_status(INVITED, true);
			return $this;
		}
		$this->setError('no one found to invite');
		return $this;
		
	}
	
	/*
	* methods for dealing with a user's registrations
	*/
	function get_transcript_for_user(User $u) {
		if (isset($u->cols['id'])) {
			$sql = "select *, r.id as registration_id from registrations r, workshops w, locations l, statuses s where r.workshop_id = w.id and w.location_id = l.id and r.status_id = s.id and r.user_id = ".$this->mres($u->cols['id'])." order by w.start desc";
			$rows = $this->query( $sql) or $this->db_error();
			$transcripts = array();
			while ($row = mysqli_fetch_assoc($rows)) {
				$transcripts[] = $row;
			}
			return $transcripts;		
		} else {
			return null;
		}
	}	
	
	public function change_status($status_id = ENROLLED, $confirm = true) {
		
		if (!$this->workshop) { $this->setError("no workshop set"); return false; } 
		if (!$this->user) { $this->setError("no user set"); return false; } 
		
		if ($status_id == ENROLLED) { // downgrade enrolled to waiting if workshop is full
			$status_id = $this->workshop->has_room() ? ENROLLED : WAITING;
		}


		$this->cols['status_id'] = $status_id;
		$this->cols['status_name'] = $this->statuses[$this->getCol('status_id')];
		$this->cols['last_modified'] = $this->mysql_now_string();

		// need to add a registration...?
		if (!$this->getCol('id')) {
			$this->cols['user_id'] = $this->user->getCol('id');
			$this->cols['workshop_id'] = $this->workshop->getCol('id');
			$this->cols['registered'] = $this->mysql_now_string();
			if (!$this->add($this->cols, 'new registration', true)) {
				return false;
			};
			$this->set_registration($this->workshop, $this->user);
		} elseif ($this->cols['status_id'] != $status_id) {
			if (!$this->save($this->cols, "registration status", true)) {
				return false;
			}
		}
		//if ($confirm) { wbh_confirm_email($wk, $u, $status_id); }
		$this->update_status_change_log($status_id);
		$this->setMessage("User ({$this->user->cols['email']}) is of status '{$this->getCol('status_name')}' for {$this->workshop->cols['showtitle']}.");
		return $this;
	}	
	
	
	public function update_status_change_log($status_id) {
		$sc = new StatusChangeLog($this->workshop, $this->user);
		$sc->update_change_log($status_id);
		return $sc;
	}
		
	
		
}	
?>