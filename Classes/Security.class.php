<?php
/*
* simple feel-good, not actually that secure security
*
* this is how you use this:

$security = new Security();
if (!$security->is_validated()) {
	echo $security->validate_user_form();
	exit;
}
	
*/

class Security {
	function get_actual_password() {
		return '1234';  
	}

	function get_submitted_password() {
		if (isset($_POST['joshua']) && $_POST['joshua']) {
		  $_SESSION['s_joshua'] = $_POST['joshua'];
		}
		return isset($_SESSION['s_joshua']) ? $_SESSION['s_joshua'] : null;
	}


	function is_validated() {
		if ($this->get_submitted_password() == $this->get_actual_password()) {
			//redirect if we have JUST validated, 
			//it avoids going to the page off a form submission
			if (isset($_REQUEST['validating']) && $_REQUEST['validating'] == 'true') {
				$script = $_SERVER['SCRIPT_NAME'] . ($_REQUEST['query'] ? "?{$_REQUEST['query']}" : '');
				header("Location: $script");
			}
			return true;
		} else {
			return false;
		}
	}

	function invalidate() {
		unset($_SESSION['s_joshua']);
		unset($_POST['joshua']);
	}

	/////////////////////////////////////////////////////////////////////
	// validate user -- user name and password stored in the code here
	/////////////////////////////////////////////////////////////////////

	function validate_user_form() {

	    return <<<VALIDFORM

	    <form action="{$_SERVER['SCRIPT_NAME']}" method="post">
		<div class="form-group">
	      <label for="joshua">Password:</label>
		  <input class="form-control" type="password" name="joshua">
	  	</div>
	      <button type="submit" class="btn btn-default">Submit</button>
		  <input type="hidden" name="validating" value="true">
		  <input type="hidden" name="query" value="{$_SERVER['QUERY_STRING']}">
	    </form>
VALIDFORM;

	}

}

?>
