<?
class User extends Model {
	
	public $key;
	protected $table_name = 'users';
	
	// user can have data 
	// may also have a key set, which means this user is logged in
	// checks for things in this order:
		// passed into constructor: keycode (logs in), userid (not logged in), email (not logged in) 
		// if not that, tries to make key from: $_REQUEST, then $_SESSION, then $_COOKIE (all logged in)
	function __construct($uid = null, $keycode = null, $email = null) {
		parent::__construct(); // sets DB object
		if ($keycode) { // did we get a key, then ignore user id
			$this->key = new Key($keycode);
			$this->setByKey($this->key);
		} elseif ($uid) { // no key, do we have a user id?
			$this->setById($uid);
		} elseif ($email) {
			$this->setByEmail($email);
		} else {
			$this->key = new Key(); // this will set a key from our argument (if it has a value), or REQUEST or SESSION, in that order
			$this->setByKey($this->key);
		}
	}
	
	/*
	* constructing users, checking status
	*/
	public function is_user_set() { // not necessarily logged in, just user data
		return $this->getCol('id');
	}
	
	public function logged_in() { // user data AND logged in (keycode set)
		if ($this->getCol('id') && isset($this->key->keycode) && $this->key->keycode) {
			return true;
		} else {
			return false;
		}
	}
		
	public function setById($id) {
		$sql = "select u.* from users u where u.id = ".$this->mres($id);
		$rows = $this->mysqli( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->cols = $row; // all fields
			// no key with this method (not logged in)
			$this->key = null;
			return $this;
		}
		return false;
	}
		
	public function setByKey(Key $key) {
		$sql = "select u.* from users u where u.ukey = '".$this->mres($key->keycode)."'";
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->cols = $row; // all fields
			$this->key = $key; // set the key, now that we found a user
			return $this;	
		}
		return false; // no user found
	}
	
	public function setByEmail($email) {
		$sql = "select u.* from users u where u.email = '".$this->mres($email)."'";
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->cols = $row; // all fields
			// no key with this method (not logged in)
			$this->key = null;
			return $this;
		}
		return false; // no user found
	}
	
		
	public function make_new_user($email) {
		$f = new Form();
		if ($f->validate_email($email)) {
			if ($this->setByEmail($email)) {
				return $this; // already made
			} else {
				$k = new Key();
				$ukey = $k->make_new_keycode();
				$sql = "insert into users (email, joined, ukey) VALUES ('".$this->mres($email)."', ".$this->mres($this->mysql_now_string()).", '".$this->mres($ukey)."')";
				$this->query( $sql) or $this->db_error();
				$this->setById(mysqli_insert_id ( $this->db )); // set the cols, but don't log in (no key set)
				return $this;
			}
		} else {
			$this->setError("Could not set up user. '$email' might not be a valid email.");
			return false; // did not set a user
		}
	}
	
	/*
	* logging in functions
	*/
	public function get_login_form() {
		$f = new Form();
		$f->text('email', null, 0, 'your email address', null, 'email');
		$f->hidden('ac', 'link', true);
		$f->submit('log in');
		return $f;			
	}
	
	public function send_login_email($email) {
		if ($this->make_new_user($email)) {
			if (!($ukey = $this->getCol('ukey'))) {
				$this->setError("no key set, can't send login link");
				return false;
			}
			$v = new View();
			$link = URL."index.php?key=$ukey";
			$body = 
"You are: {$this->getCol('email')}
Use this link to log in:
$link

Thanks!
-Will
".strip_tags($v->renderSnippet('faq'));

			mail($this->getCol('email'), "Log in to 'Will Hines practices'", $body, "From: ".WEBMASTER);
			$this->setMessage("Thanks! I've sent a link to your {$u->getCol('email')}. Click it! (check your spam folder).");
			return $this;				
		} else {
			$this->setError("Couldn't get a user for '$email.' Is it a valid email?");
			return false;
		}
	}
	
	
	public function log_out() {
		unset($_SESSION['s_key']);
		unset($_COOKIE['c_key']);
		setcookie("c_key", "", time()-3600);
		$this->key = new Key();
		$this->cols = array();
		$this->setMessage('You are logged out.');
		return $this;
	}
	
	// deletes user from database, though it does not destroy current object
	// so current object is kinda like a ghost
	public function delete_user() {
		if ($this->is_user_set()) {
			$uid = $this->getCol('id');
			$sql = "delete from registrations where user_id = ".$this->mres($uid);
			$this->query($sql) or $this->db_error();
			$sql = "delete from users where id = ".$this->mres($uid);
			$this->query($sql) or $this->db_error();
			$sql = "delete from status_change_log where user_id = ".$this->mres($uid);
			$this->query($sql) or $this->db_error();
			$this->setMessage("Deleted user {$this->getCol('email')}");
		} else {
			$this->setError("No user set to delete");
			return false;
		}	
	}
	
	/*
	* functions to deal with user's registration
	*/
	public function get_transcript() {
		$r = new Registration();
		return $r->get_transcript_for_user($this); // an array of rows from registrations table
		
	}
	

	
	
	/*
	* functions to update user info 
	*/
	public function reset_current_key() {
		if ($this->logged_in()) {
			$this->cols['ukey'] = $this->key->make_new_keycode(); // get new key (just generates the string)
			$this->save($this->cols); // save it to database
			$this->send_login_email($this->getCol('email')); // send email to user with new key
			$this->log_out(); // log 'em out
			$this->setMessage('I made a new key, sent it to you in an email and then logged you out.');
			return $key;
		} else {
			$this->setError("Can't reset key because you are not logged in.");
			return false;
		}
	}
	
	
	
	public function get_text_preferences_form() {
		$c = new Carriers();
		$f = new Form();
		$f->hidden('id', $this->getCol('id'));
		$f->hidden('ac', 'textup');
		$f->checkbox('send_text', 1, 'Send text updates?', $this->getCol('send_text'));
		$f->drop('carrier_id', $c->get_carriers_dropdown(),  $this->getCol('carrier_id'), 'phone network');
		$f->text('phone', $this->getCol('phone'), 'phone number', null, '(just 10 numbers - no dashes)', 'numbers');
		$f->submit('Update Text Preferences');
		
		// validation that involves more than one field taken together
		if (!$f->error && ($f->params['send_text'] == 1 && !$f->params['carrier_id'])) {
			$f->setError("If you want to get texts, you must pick a phone network.");
		}
		if (!$f->error && ($f->params['send_text'] == 1 && !$f->params['phone'])) {
			$f->setError("If you want to get texts, you must set a phone number.");
		}
		return $f;			
	}
	
	public function get_change_email_form() {
		$f = new Form();
		$f->hidden('ac', 'cemail');
		$f->text('newemail', $this->getCol('newemail'), 'new email address', null, null, 'email');
		$f->submit('Change Email');
		return $f;
	}
	
	// first half of changing email
	// sets requested email as "new email" in user row
	// then sends an email to new email account
	public function change_email_request() {
		if ($this->logged_in()) {
			$params = $this->get_change_email_form()->get_values();
			
			$new_u = new User(null, null, $params['newemail']); 	
			if ($new_u->is_user_set()) {
				$this->setError("Your requested new email '{$params['newemail']}' is already being used.");
				return false;
			} 

			$this->cols['new_email'] = $params['newemail'];
			$this->save($this->cols, 'your new email address', true); // save it silently
			$link = URL."index.php?key={$this->key->keycode}&ac=concemail";
			$ebody = "You requested to start using the email '{$this->cols['new_email']}' as your email at the Will Hines practices web site. If that's true, please confirm by clicking this link :\n\n$link";
			mail($this->cols['new_email'], 'request to change email at Will Hines practices', $ebody, "From: ".WEBMASTER);
			$this->setMessage("Okay, a link has been sent to the new email address ({$this->cols['new_email']}). Check your spam folder if you don't see it.");
			return $this;
		} else {
			$this->setError("You're not logged in, so I can't change your email.");
			return false;
		}
	}
	
	// second half of changing email
	// actually does the change
	public function change_email() {
		if ($this->logged_in()) {
		
			// return false if new email is already a user
			$new_u = new User(null, null, $this->getCol('new_email')); 			
			if ($new_u->is_user_set()) {
				$this->setError("Your requested new email '{$new_u->getCol('email')}' is already being used.");
				return false;
			} 
			// rename old email
			$if_successful = "Email changed from '{$this->getCol('email')}' to '{$this->getCol('new_email')}'";
			$this->cols['email'] = $this->cols['new_email'];
			if ($this->save($this->cols, 'your new email address', true)) {
				$this->setMessage($if_successful);
			};
			return true;
			
		} else {
			$this->setError("You're not logged in, so I can't change your email.");
			return false;
		}
	}
			
}	
?>