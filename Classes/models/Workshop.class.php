<?php
class Workshop extends Model {
		
		
	protected $table_name = 'workshops';
		
	function __construct($id = null) {
		parent::__construct(); // set DB
		if ($id) {
			$this->setById($id);
		}
	}
	
	/*
	* methods to create object, set up/format workshop data
	*/
	public function setById($id) {
				
		$sql = "select w.*, l.place, l.lwhere from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id where w.id = ".$this->mres($id);
		
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->cols = $row; // data from table set in to model
			$this->format_workshop_startend(); // format title, time stamps
			$this->set_registration_totals(); // figure enrolled, waiting, dropped, invited
			$this->set_type(); // past? sold out? open?
			$this->check_last_minuteness(); // note who is registered while it's sold out
			
			$this->check_waiting(); // make sure waiting list has been tended to
			return $this;
		}
		$this->setError("couldn't find workshop in DB");
		return $this;		
		
	}
	
	private function set_registration_totals() {
		$r = new Registration();
		$this->cols['enrolled'] = $r->get_registration_totals($this);
		$this->cols['invited'] = $r->get_registration_totals($this, INVITED);
		$this->cols['waiting'] = $r->get_registration_totals($this, WAITING);
		$this->cols['open'] = ($this->cols['enrolled'] >= $this->cols['capacity'] ? 0 : $this->cols['capacity'] - $this->cols['enrolled']);
	}

	private function set_type() {
		if (strtotime($this->cols['start']) < strtotime('now')) { 
			$this->cols['type'] = 'past'; 
		} elseif ($this->has_room()) { 
			$this->cols['type'] = 'open';
		} else {
			$this->cols['type'] = 'soldout'; 
		}
	}
	
	// pass in the workshop row as it comes from the database table
	// add some columns with date / time stuff figured out
	public function format_workshop_startend($cols = null) {
		if ($cols) {
			$this->cols = $cols;
		}
		if (date('Y', strtotime($this->cols['start'])) != date('Y')) {
			$this->cols['showstart'] = date('D M j, Y - g:ia', strtotime($this->cols['start']));
		} else {
			$this->cols['showstart'] = date('D M j - g:ia', strtotime($this->cols['start']));
		}
		$this->cols['showend'] = date('g:ia', strtotime($this->cols['end']));
		$this->cols['showtitle'] = "{$this->cols['title']} - {$this->cols['showstart']}-{$this->cols['showend']}";
		$this->cols['when'] = "{$this->cols['showstart']}-{$this->cols['showend']}";
	}	


	private function check_last_minuteness() {
	
		/* 
			there's two flags:
				1) workshops have "sold_out_late" meaning the workshop was sold out within $late_hours of the start. We update this to 1 or 0 everytime the web site selects the workshop info from the db.
				2) registrations have a "while_sold_out" flag. if it is set to 1, then you were enrolled in this workshop while it was sold out within $late_hours of its start. we also check this every time we select the workshop info. but this never gets set back to zero. 
		*/ 
			
		$hours_left = (strtotime($this->cols['start']) - strtotime('now')) / 3600;
		if ($hours_left > 0 && $hours_left < LATE_HOURS) {
			// have we never checked if it's sold out
			if ($this->cols['sold_out_late'] == -1) {
				if ($this->cols['type'] == 'soldout') {
					$sql = 'update workshops set sold_out_late = 1 where id = '.$this->db->mres($this->cols['id']);
					$this->query( $sql) or $this->db_error();
					$this->cols['sold_out_late'] = 1;
				
					// note which registrations existed while things were sold out late
					$r = new Registration();
					$r->registered_while_sold_out($this);
				
				} else {
					
					// note that workshop is NOT sold out late
					$sql = 'update workshops set sold_out_late = 0 where id = '.$this->db->mres($this->cols['id']);
					$this->query( $sql) or $this->db_error();
					$this->cols['sold_out_late'] = 0;
				}
			}
		}
		return $this;
	}

	public function has_room() {
		 if (($this->getCol('enrolled')+$this->getCol('invited')+$this->getCol('waiting')) < $this->getCol('capacity')) {
			 return true;
		 } else {
			 return false;
		 }
	}
	
	/*
	* methods dealing with the students signed up for this workshop
	*/
	public function check_waiting() {		
		if ($this->cols['type'] == 'past') {
			return 'Workshop is in the past';
		}
		
		// open spots plus people on waiting list?
		while (($this->cols['enrolled']+$this->cols['invited']) < $this->cols['capacity'] && $this->cols['waiting'] > 0) {
			
			// invite next person
			$r = new Registration();
			$r = $r->invite_next_waiting($this);
			$this->setById($this->cols['id']); // refresh this object so we get new totals
			if ($r->message) { $this->setMessage($r->message); }
		}
		if ($this->message) {
			return $this->message;
		} else {
			return "No invites sent.";
		}
	}
	
	
	
	/*
	* methods to update data in the workshop
	*/
	public function get_workshop_form($add = false) {
		
		$l = new Locations();
		$f = new Form();
		$f->text('title', $this->getCol('title'));
		$f->drop('location_id', $l->get_locations_dropdown(), $this->getCol('location_id'), 'Location');
		$f->text('start', $this->getCol('start'));
		$f->text('end', $this->getCol('end'));
		$f->text('cost', $this->getCol('cost'));
		$f->text('capacity', $this->getCol('capacity'));
		$f->textarea('notes', $this->getCol('notes'));
		$f->text('when_public', $this->getCol('when_public'), 'When Public');
		$f->hidden('id', $this->getCol('id'), null, false);
		if ($add) {
			$f->hidden('ac', 'add');
			$f->submit('add workshop');
		} else {
			$f->hidden('ac', 'edit');
			$f->submit('update workshop');
		}
		return $f;
	}	
	
	public function add_student_form() {
		$f = new Form();
		$f->hidden('ac', 'enroll');
		$f->text('email', '', 0);
		$f->radio('con', array('1' => 'confirm', '0' => 'don\'t'), '0');
		$f->hidden('wid', $this->getCol('id'));
		$f->submit('Enroll');
		return $f;
	}
	
	
	public function get_workshops_list($admin = false) {
		
		$rows_to_show = null;
		
		$sql = 'select w.*, l.place, l.lwhere 
		from workshops w LEFT OUTER JOIN locations l on w.location_id = l.id ';
		$sql .= $admin ? " order by start desc" : " order by start asc";
		$rows = $this->query( $sql) or $this->db_error();
		$i = 0;

		while($row = mysqli_fetch_assoc($rows)) {
			$this->setById($row['id']);

			if (strtotime($row['start']) < time() && !$admin) { continue; }
			if (strtotime($row['when_public']) > time() && !$admin) { continue; }
		
			$rows_to_show[] = $this->cols;
		}
		return $rows_to_show;
	}
		
	
}	
	
	
	
	
	
?>