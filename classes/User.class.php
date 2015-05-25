<?
class User extends Model {
	
	public $key;
	
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
		if ($this->cols['id'] && $this->key->keycode) {
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
		}
		return $this;
	}
		
	public function setByKey(Key $key) {
		$sql = "select u.* from users u where u.ukey = '".$this->mres($key->keycode)."'";
		$rows = $this->query( $sql) or $this->db_error();
		while ($row = mysqli_fetch_assoc($rows)) {
			$this->cols = $row; // all fields
			$this->key = $key; // set the key, now that we found a user
		}
		return $this;
	
	}
	
	public function get_login_form() {
		$f = new Form();
		$f->text('email', null, 0, 'your email address', null, 'email');
		$f->hidden('ac', 'link');
		$f->submit('log in');
		return $f;			
	}
	
	
	
	public function get_text_preferences_form() {
		$c = new Carriers();
		$f = new Form();
		$f->hidden('uid', $this->getCol('id'));
		$f->hidden('ac', 'updateu');
		$f->hidden('v', 'text');
		$f->checkbox('send_text', 1, 'Send text updates?', $this->getCol('send_text'));
		$f->drop('carrier_id', $c->get_carriers_dropdown(),  $this->getCol('carrier_id'), 'phone network');
		$f->text('phone', $this->getCol('phone'), 'phone number', null, '(just 10 numbers - no dashes)', 'numbers');
		$f->submit('Update Text Preferences');
		
		if (!$f->error && ($f->params['send_text'] == 1 && !$f->params['carrier_id'])) {
			$f->setError("If you want to get texts, you must pick a phone network.");
		}
		if (!$f->error && ($f->params['send_text'] == 1 && !$f->params['phone'])) {
			$f->setError("If you want to get texts, you must set a phone number.");
		}
		
		return $f;			
		
	}

	public function get_transcript() {
		$r = new Registration();
		return $r->get_transcript_for_user($this);
		
	}

	public function log_out() {
		unset($_SESSION['s_key']);
		$this->key = new Key();
		$this->cols = array();
		return $this;
	}
			
}	
?>