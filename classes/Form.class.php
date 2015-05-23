<?php
class Form extends WBHObject {

	public $output;
	
	function __construct() {
		$this->output = null;
	}
	
	public function get_form($option = null) {
		$xtra_class = '';
		if ($option == 'inline') {
			$xtra_class = 'form-inline';
		}
		
		$body = "<form class='$xtra_class' action='{$_SERVER['SCRIPT_NAME']}' method='post'>\n";
		$body .= $this->output;
		$body .= "</form>\n";
		
		return $body;
	}
	
	private function check_value($id, $default = null, $validation = null) {
	
		$this->error = null; // clear out errors
		
		$value = isset($_REQUEST[$id]) ? $_REQUEST[$id] : $default;
		
		if ($value && $validation == 'email') {
			if (!$this->validate_email($value)) {
				$this->setError("not a valid email");
			}
		} elseif ($value && is_numeric($validation)) {
			if (strlen($value) > $validation) {
				$this->setError("this field can not be longer than $validation characters");
			}
		} elseif ($validation == 'required') {
			if (!$value) {
				$this->setError('this field is required');
			}
		} elseif ($value && $validation == 'numbers') {
			if (!preg_match('/^\d+$/', $value)) {
				$this->setError('can use only numbers on this field');
			}
		} elseif ($value && $validation == 'letters') {
			if (!preg_match('/^[:alpha:]+$/', $value)) {
				$this->setError('can use only letters on this field');
			}
			
		}
		return $value;
		
	}
	
	function text($id, $value = null, $label = null, $placeholder = null, $help = null, $validation = null) {
		
		$value = $this->check_value($id, $value, $validation);
		$l = $this->label($label, $id);	
		
		$this->add_to_output($this->form_group_start($this->error));		
		$this->add_to_output("{$l}<input class='form-control' type='text' id=\"$id\" name=\"$id\" value=\"$value\" ".($placeholder ? "placeholder='$placeholder'" : '').">");
		$this->add_to_output($this->form_help_block($help, $this->error));		
		$this->add_to_output("</div>\n");
	
		return $this;
	}

	function textarea($id, $value = null, $label = null, $rows = 5, $cols = 40, $help = null, $validation = null) {

		$value = $this->check_value($id, $value, $validation);
		$l = $this->label($label, $id);	

		$this->add_to_output(form_group_start($this->error));
		$this->add_to_output("{$l} <textarea class='form-control' id='{$id}' name='{$id}' cols='{$cols}' rows='{$rows}'>{$value}</textarea>");
		$this->add_to_output($this->form_help_block($help, $this->error));		
		$this->add_to_output("</div>\n");	
		return $this;
	}
 
	function hidden($id, $value = '') {
		$value = $this->check_value($id, $value);
		$this->add_to_output("<input type='hidden' name='$id' value='$value'>\n");
		return $this;
	}

	function submit($value = 'Submit') {
		$this->add_to_output("<button type=\"submit\" class=\"btn btn-primary\">{$value}</button>\n");
		return $this;
	}

	function drop($name, $opts, $selected = null, $label = null, $help = null, $validation = null) {
		$value = $this->check_value($id, $selected, $validation);
		$l = $this->label($label, $name);

		$this->add_to_output($this->form_group_start($this->error));	
		$$this->add_to_output("{$l} <select class='form-control' name='$name' id='$name'><option value=''></option>\n");
		foreach ($opts as $id => $show) {
			$this->add_to_output("<option value='$id'");
			if ($id == $selected) { $this->add_to_output(" SELECTED "); } 
			$this->add_to_output(">$show</option>\n");
		}
		$this->add_to_output("</select>");
		$this->add_to_output($this->form_help_block($help, $this->error));		
		$this->add_to_output("</div>\n");
		return $this;
	}

	function multi_drop($name, $opts, $selected = null, $label = null, $size = 10, $help = null) {
		$l = $this->label($label, $name);
	
		$this->add_to_output($this->form_group_start());	
		$this->add_to_output("{$l} <select size='$size' multiple class='form-control' name='{$name}".'[]'."' id='$name'><option value=''></option>\n");
		foreach ($opts as $id => $show) {
			$this->add_to_output("<option value=\"$id\"");
			if (is_array($selected)) {
				foreach ($selected as $sel) {
					if ($id == $sel) { $this->add_to_output(" SELECTED "); } 
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

	function checkbox($name, $value, $label = null, $checked = false, $multiple = false, $help = null) {
		$label = wbh_figure_label($label, $name, false);
		if ($multiple) { $name = "{$name}[]"; }
		$this->add_to_output("<div class='checkbox-inline'><label class=\"checkbox inline\">
		  <input type=\"checkbox\" name=\"{$name}\" value=\"{$value}\" ".($checked ? 'checked' : '').">
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
	
	private function validate_email($emailaddress) {
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

}
?>