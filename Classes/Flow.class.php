<?php
namespace Classes;	
class Flow extends WBHOBject {
	
	public $whiteList;
	public $params;
	public $data; // data for the view
	public $controller; // which controller to call
	public $method; // which method to call
	
	function __construct($vars = null) {
		$this->params = $this->parse_incoming_url();
		if ($vars) {
			$this->setWhiteList($vars);
		}
	}
	
	
	//parse incoming URL into stuff, expecting "controller/method/param1/param2"
	function parse_incoming_url() {
	  $params = array();
	  if (isset($_SERVER['REQUEST_URI'])) {
	    $request_path = explode('?', $_SERVER['REQUEST_URI']);
	    $params['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
	    $params['call_utf8'] = substr(urldecode($request_path[0]), strlen($params['base']) + 1);
	    $params['call'] = utf8_decode($params['call_utf8']);
	    if ($params['call'] == basename($_SERVER['PHP_SELF'])) { $params['call'] = ''; }
	    $params['call_parts'] = explode('/', $params['call']);
	    $params['query_utf8'] = (isset($request_path[1]) ? urldecode($request_path[1]): '');
	    $params['query'] = (isset($request_path[1]) ? utf8_decode(urldecode($request_path[1])) : '');
	    $vars = explode('&', $params['query']);
	    foreach ($vars as $var) {
	      $t = explode('=', $var);
	      $params['query_vars'][$t[0]] = (isset($t[1]) ? $t[1] : '');
	    }
	  }
	  //echo '<pre>'.print_r($params, true).'</pre>';
	  
	  // check for controller
	  $looking_for = 'Classes\Controllers\\'.ucfirst($params['call_parts'][0]);
	  if (isset($params['call_parts'][0]) && class_exists($looking_for)) {
			$this->controller = $params['call_parts'][0];

			  // check for method

	  } else {
		 // couldn't find controller
	  }
	  
	  
	  
	  $this->params = $params;
	  return $params;
	}
		
	public function setWhiteList($whiteList) {
		if (is_array($whiteList)) {
			$this->whiteList = $whiteList;
			foreach ($this->whiteList as $va) {
				$this->get($va);
			}
		}
	}

	public function get($va) {
		$this->params[$va] = isset($_REQUEST[$va]) ? $_REQUEST[$va] : '';
	}


}	
	
	
?>