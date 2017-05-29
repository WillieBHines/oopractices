<?
namespace Classes\Models;

class Registration extends \Classes\Model {
	
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
		$sql2 = "select r.* from registrations r where r.workshop_id = ".$this->mres($this->cols['workshop_id'])." and r.status_id = '".$this->mres($this->cols['status_id'])."' order by last_modified";
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
		$sql = "select * from registrations where workshop_id = ".mres($wk->cols['id'])." and status_id = '".WAITING."' order by last_modified limit 1";
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->set_registration($wk, new User($row['user_id']));
			$this->change_status(INVITED, true);
			return $this;
		}
		$this->setError('no one found to invite');
		return false;
		
	}
	
	// returns a three-deep array
	// first level id = status id
	// second level:  id = user id 
	// third level: array of values from the db for that user id
	public function get_all_students(Workshop $wk) {
		$students = array();
		foreach ($this->statuses as $stid => $stname) {
			$students[$stid] = $this->get_students_by_status($wk, $stid);
		}
		return $students;
	}
	
	// returns a two-deep array
	// first level: user id
	// second level an array of the db row for that id
	public function get_students_by_status(Workshop $wk, $status_id = ENROLLED) {
		$sql = "select u.*, r.status_id,  r.attended, r.registered, r.last_modified  from registrations r, users u where r.workshop_id = ".$this->mres($wk->getCol('id'));
		if ($status_id) { $sql .= " and status_id = '".$this->mres($status_id)."'"; }
		$sql .= " and r.user_id = u.id order by status_id, last_modified";
		$rows = $this->query( $sql) or $this->db_error();
		$stds = array();
		while ($row = mysqli_fetch_assoc($rows)) {
			$stds[$row['id']] = $row;
		}
		return $stds;
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
	
	/*
	* "change_status" is an important method. 
	* we use this to do the main work of this application
	* 
	* if you're trying to enroll, it makes sure there's room or that you're invited
	* it then enrolls or drops
	* it sends confirmation emails, texts and then updates a change log
	* it then prompts the workshop object to refresh itself and send invites to waiting list if need be
	*/
	public function change_status($target_status_id = ENROLLED, $confirm = true) {
		
		if (!$this->workshop) { $this->setError("no workshop set"); return false; } 
		if (!$this->user) { $this->setError("no user set"); return false; } 
		
		$current_status_id = $this->getCol('status_id');
		
		
		// enrolling downgrades to waiting if 
		// 1) it's full or 
		// 2) others are waiting and you're not invited
		if ($target_status_id == ENROLLED) { 			
			if ($this->workshop->has_room()) {
				if ($this->workshop->getCol('waiting') > 0 || $current_status_id != INVITED) {
					$target_status_id = WAITING;
				}
			} else {
				$target_status_id = WAITING;
			}
		}

		// are we already this status?
		if ($target_status_id == $current_status_id) {
			$this->setMessage("You are already ".$this->statuses[$target_status_id]." for this workshop.");
			return $this;
		}

		// make changes to object
		$this->cols['status_id'] = $target_status_id;
		$this->cols['status_name'] = $this->statuses[$target_status_id];
		$this->cols['last_modified'] = $this->mysql_now_string();

		// save changes to database 
		if (!$this->getCol('id')) { // make a new row?
			$this->cols['user_id'] = $this->user->getCol('id');
			$this->cols['workshop_id'] = $this->workshop->getCol('id');
			$this->cols['registered'] = $this->mysql_now_string();
			if (!$this->add($this->cols, 'new registration', true)) {
				return false;
			};
			$this->set_registration($this->workshop, $this->user);
		} else { // just save existing row
			if (!$this->save($this->cols, "registration", true)) {
				return false;
			}
		}
		
		// send confirmations, update change log. don't stop on failure for these
		if ($confirm) { 
			$this->send_confirm_email();
			$this->send_confirm_text();
		}
		$this->update_status_change_log($target_status_id);
		$this->setMessage("User ({$this->user->cols['email']}) is now status '{$this->getCol('status_name')}' for {$this->workshop->cols['showtitle']}.");
		
		// refresh workshop data, this will check the waiting list and send invites if need be
		$this->workshop->setById($this->getCol('workshop_id')); 
		
		return $this;
	}		
	
	public function update_status_change_log($status_id) {
		$sc = new StatusChangeLog($this->workshop, $this->user);
		$sc->update_change_log($status_id);
		return $sc;
	}
		
	
	/*
	* some methods for sending emails and texts
	*/
	public function send_confirm_email() {
		$v = new View();		

		$data['admin'] = false;
		$data['wk'] = $this->workshop;
		$data['u'] = $this->user;
		$data['r'] = $this;
		$data['sc'] = $_SERVER['SCRIPT_NAME'];
		$data['faq'] = strip_tags($v->renderSnippet('faq'));
		$data['late_warning'] = $v->renderSnippet('dropping_late_warning');

		$body = $v->renderSnippet('confirmation_email', $data);

		if (!mail($this->user->getCol('email'), "{$this->getCol('status_name')} for {$this->workshop->getCol('title')}", $body, "From: ".WEBMASTER)) {
			$this->setError('Failed to send confirmation email.');
			return false;
		} else {
			return true;
		}
	}

	public function send_confirm_text() {
		if ($this->user->getCol('send_text')) {
			
			$url = URL."index.php?key={$this->user->key->keycode}&wid={$this->workshop->getCol('id')}";
			$c = new Carriers();
			$short_url = $c->shorten_link($url);

			$show_name = $this->workshop->getCol('title');
			switch ($this->getCol('status_id')) {
				case ENROLLED:
					$point = "You are ENROLLED in {$show_name}.";
					break;
				case WAITING:
					$point = "You are WAITING (spot {$this->getCol('rank')}) for {$show_name}.";
					break;
				case INVITED:
					$point = "You are INVITED for {$show_name}.";
					break;
				case DROPPED:
					$point = "You have DROPPED from {$show_name}";
					break;
			}
			
			if (!$this->user->send_text($point.' for more info: '.$short_url)) {
				$this->setError($this->user->error);
				return false;
			} else {
				return true;
			}
		}
	}

}	
?>