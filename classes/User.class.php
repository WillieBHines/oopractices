<?
class User extends Model {
	
	public $key;
	protected $table_name = 'users';
	
	// set by key if there is one, or if it was passed in
	// else check user id
	// else this will be an empty object
	function __construct($uid = null, $keycode = null) {
		parent::__construct(); // sets DB object
		$this->key = new Key($keycode); // this will set a key from our argument (if it has a value), or REQUEST or SESSION, in that order
		if ($this->key->keycode) { // did we get a key, then ignore user id
			$this->setByKey($this->key);
		} elseif ($uid) { // no key, do we have a user id?
			$this->setById($uid);
		}
	}
	
	public function logged_in() {
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
				$sql = "insert into users (email, joined, ukey) VALUES ('".$this->mres($email)."', now(), '".$this->mres($ukey)."')";
				$this->query( $sql) or $this->db_error();
				$this->setById(mysqli_insert_id ( $this->db )); // set the cols, but don't log in (no key set)
				return $this;
			}
		} else {
			$this->setError("Could not set up user. '$email' might not be a valid email.");
			return false; // did not set a user
		}
	}
	
	public function send_login_email($email) {
		if ($this->make_new_user($email)) {
			if (!($ukey = $this->getCol('ukey'))) {
				$this->setError("no key set, can't send link");
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

			return mail($this->getCol('email'), "Log in to 'Will Hines practices'", $body, "From: ".WEBMASTER);				
		} else {
			return false;
		}
	}
	
	public function reset_current_key() {
		if ($this->logged_in()) {
			$this->cols['ukey'] = $this->key->make_new_keycode(); // get new key (just generates the string)
			$this->save($this->cols); // save it to database
			$this->send_login_email($this->getCol('email')); // send email to user with new key
			$this->log_out(); // log 'em out
			return $key;
		}
	}
	
	
	public function get_login_form() {
		$f = new Form();
		$f->text('email', null, 0, 'your email address', null, 'email');
		$f->hidden('ac', 'link', true);
		$f->submit('log in');
		return $f;			
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

	public function get_transcript() {
		$r = new Registration();
		return $r->get_transcript_for_user($this);
		
	}

	public function log_out() {
		unset($_SESSION['s_key']);
		unset($_COOKIE['c_key']);
		setcookie("c_key", "", time()-3600);
		$this->key = new Key();
		$this->cols = array();
		return $this;
	}
			
}	
?>