<?php
namespace Classes;

class Form extends WBHObject {

	public $output;
	public $params;
	private $element_error;
	private $submitted_id = 'submitted';
	
	function __construct() {
		$this->output = null;
	}
	
	public function get_form($action = null, $option = null) {
		
		
		$this->hidden($this->submitted_id, '1');	// so we can know if form was submitted	
		$action = ($action ?  $action : $_SERVER['SCRIPT_NAME']);
		$xtra_class = ($option == 'inline') ? 'form-inline' : '';

		$body = '';
		if ($this->is_submitted() && $this->error) { // form was submitted we can check for errors
			$body .= "<div class='alert alert-danger'>{$this->error}</div>\n";
		}
		$body .= "<form class='$xtra_class' action='{$action}' method='post'>\n";
		$body .= $this->output;
		$body .= "</form>\n";
		
		return $body;
	}
	
	// after the form has checked for all the data being passed in
	public function get_values() {
		return $this->params;
	}
	
	private function check_value($id, $default = null, $validation = null, $ignore_request = null) {
	
		$this->element_error = null; // clear out errors
		
		$value = isset($_REQUEST[$id]) && !$ignore_request ? $_REQUEST[$id] : $default;
		
		$this->params[$id] = $value; // for a model to save
		
		if ($value && $validation == 'email') {
			if (!$this->validate_email($value)) {
				$this->element_error = "not a valid email";
			}
		} elseif ($value && is_numeric($validation)) {
			if (strlen($value) > $validation) {
				$this->element_error = "this field can not be longer than $validation characters";
			}
		} elseif ($validation == 'required') {
			if (!$value) {
				$this->element_error = "this field is required";
			}
		} elseif ($value && $validation == 'numbers') {
			if (!preg_match('/^\d+$/', $value)) {
				$this->element_error = "can use only numbers on this field";
			}
		} elseif ($value && $validation == 'letters') {
			if (!preg_match('/^[:alpha:]+$/', $value)) {
				$this->element_error = "can use only letters on this field";
			}
			
		}
		if ($this->element_error) {
			$this->setError("There's an error in the form below."); // form fails if an element fails
		}
		return $value;
		
	}
	
	function text($id, $value = null, $label = null, $placeholder = null, $help = null, $validation = null) {
		
		$value = $this->check_value($id, $value, $validation);
		$l = $this->label($label, $id);	
		
		$this->add_to_output($this->form_group_start($this->element_error));		
		$this->add_to_output("{$l}<input class='form-control' type='text' id=\"$id\" name=\"$id\" value=\"$value\" ".($placeholder ? "placeholder='$placeholder'" : '').">");
		$this->add_to_output($this->form_help_block($help, $this->element_error));		
		$this->add_to_output("</div>\n");
	
		return $this;
	}

	function textarea($id, $value = null, $label = null, $rows = 5, $cols = 40, $help = null, $validation = null) {

		$value = $this->check_value($id, $value, $validation);
		$l = $this->label($label, $id);	

		$this->add_to_output($this->form_group_start($this->element_error));
		$this->add_to_output("{$l} <textarea class='form-control' id='{$id}' name='{$id}' cols='{$cols}' rows='{$rows}'>{$value}</textarea>");
		$this->add_to_output($this->form_help_block($help, $this->element_error));		
		$this->add_to_output("</div>\n");	
		return $this;
	}
 
	function hidden($id, $value = '', $ignore_request = true) {
		$value = $this->check_value($id, $value, null, $ignore_request);
		$this->add_to_output("<input type='hidden' name='$id' value='$value'>\n");
		return $this;
	}

	function submit($value = 'Submit') {
		$this->add_to_output("<button type=\"submit\" class=\"btn btn-primary\">{$value}</button>\n");
		//$this->submit_id = 
		return $this;
	}

	function drop($id, $opts, $selected = null, $label = null, $help = null, $validation = null) {
		$value = $this->check_value($id, $selected, $validation);
		$l = $this->label($label, $id);

		$this->add_to_output($this->form_group_start($this->element_error));	
		$this->add_to_output("{$l} <select class='form-control' name='$id' id='$id'><option value=''></option>\n");
		foreach ($opts as $oid => $show) {
			$this->add_to_output("<option value='$oid'");
			if ($oid == $selected) { $this->add_to_output(" SELECTED "); } 
			$this->add_to_output(">$show</option>\n");
		}
		$this->add_to_output("</select>");
		$this->add_to_output($this->form_help_block($help, $this->element_error));		
		$this->add_to_output("</div>\n");
		return $this;
	}

	function multi_drop($id, $opts, $selected = null, $label = null, $size = 10, $help = null, $validation = null) {
		$value = $this->check_value($id, $selected, $validation);
		$l = $this->label($label, $id);
	
		$this->add_to_output($this->form_group_start());	
		$this->add_to_output("{$l} <select size='$size' multiple class='form-control' name='{$id}".'[]'."' id='$id'><option value=''></option>\n");
		foreach ($opts as $oid => $show) {
			$this->add_to_output("<option value=\"$oid\"");
			if (is_array($selected)) {
				foreach ($selected as $sel) {
					if ($oid == $sel) { $this->add_to_output(" SELECTED "); } 
				}
			} else {
				if ($id == $sel) { $this->add_to_output(" SELECTED "); } 
			}
			$this->add_to_output(">$show</option>\n");
		}
		$this->add_to_output("</select>");
		$this->add_to_output($this->form_help_block($help));		
		$this->add_to_output("</div>");

		return $this;
	}


	function radio($name, $opts, $selection = null, $help = null) {
		$value = $this->check_value($name, $selection);
		$i = 1;
		$this->add_to_output("<div class='radio-inline'>");
		foreach ($opts as $id => $label) {
			$ch = ($id == $selection && !is_null($selection) ? 'checked' : '');
			$this->add_to_output('<label for="'.$name.'"><input class="form-control" type="radio" id="'.$name.'" name="'.$name.'" id="radio'.$i.'" value="'.$id.'" '.$ch.'> '.$label.'</label> ');
			$i++;
		}
		$this->add_to_output($this->form_help_block($help));	
		$this->add_to_output("</div>\n");
		return $this;
	}

	function checkbox($id, $value, $label = null, $checked = false, $multiple = false, $help = null) {
		$value = $this->check_value($id, $checked);
		$label = $this->figure_label($label, $id, false);
		if ($multiple) { $id = "{$id}[]"; }
		$this->add_to_output("<div class='checkbox-inline'><label class=\"checkbox inline\">
		  <input type=\"checkbox\" name=\"{$id}\" value=\"{$value}\" ".($checked ? 'checked' : '').">
		  {$label}
		</label></div>");
		return $this;
	}

	private function label($label, $id, $colon = false) {
		$l = $this->figure_label($label, $id, $colon = false);
		if ($l) { 
			return "<label for='{$id}'>{$l}</label>\n";
		}
		return null;
	}

	private function figure_label($label, $id, $colon = false) {
		if ($label === 0) { 
			return '';
		} else {
			return ($label ? $label : ucwords($id)).($colon ? ': ' : '');
		}	
	}

	// next two subs deal with error messages
	function form_group_start($error = null) {
		if ($error) {
			return "<div class='form-group has-error'>";
		} else {
			return "<div class='form-group'>";
		}
	} 

	function form_help_block($help = null, $error = null) {
		if ($error) {
			return "<p class='help-block text-danger'>$error</p>";
		}
		if ($help) {
			return "<p class='help-block'>$help</p>";
		}
	
	}
	
	public function validate_email($emailaddress) {
		$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

		if (preg_match($pattern, $emailaddress) === 1) {
			return true;
		} else {
			return false;
		}
	}
	
	private function add_to_output($stuff) {
		$this->output .= $stuff;
		return $this;
	}
	
	private function is_submitted() {
		if (isset($_REQUEST[$this->submitted_id])) {
			return true;
		} else {
			return false;
		}
	}
}
?>