<?php

/* An API for returning information

	Notice that the classname matches the filename, which will match the URLs it serves.

	This class will service requests like:

		example.com/info.json
		example.com/info/some/path.json

	The requests will be mapped to class functions named <method>_<thing>.<resource type>.
*/
class info extends API {

	public function __construct() {
		parent::__construct('presto-example-1');
		// other startup would go here
	}

	// info.json (root get request)
	public function get($p, $o, $b, $t) {
	
		$this->restrictTo(array('json', 'js'));
		
		if (count($p) != 0)
			throw new \Exception('Too many parameters', 400); // will result in a proper 400 HTTP status

		return array('pass' => 'Simple GET'); // will be returned as json, if json is requested
	}

	// info/params.json (params tests)
	public function get_params($p, $o, $b, $t) {
	
		$this->restrictTo(array('json', 'js'));
		$c = count($p);
		
		switch ($c) {
			case 0:
				return array('pass' => 'Object GET');
				
			case 1: 
			case 6:
				return array('pass' => "Object GET - $c parameters", 'parameters' => $p);
				
			default:
				throw new \Exception("Invalid number of parameters ($c)", 400);
		}
	}

	// info/params.json (params tests)
	public function get_options($p, $o, $b, $t) {
	
		$this->restrictTo(array('json', 'js'));
		$c = count($o);
		
		switch ($c) {
			case 0:
				return array('pass' => 'Object GET');
				
			case 1: 
			case 6:
				return array('pass' => "Object GET - $c options", 'options' => $o);
				
			default:
				throw new \Exception("Invalid number of options ($c)", 400);
		}
	}
	
	// info/params.json (params tests)
	public function post($p, $o, $b, $t) {
	
		$this->restrictTo(array('json', 'js'));
		
		if (empty($b))
			throw new \Exception('Missing POST body', 500);
					
		return array('pass' => "Simple POST", 'body' => $b);
	}	
	
	// Test custom header values
	public function get_header_test($ctx) {
		$this->restrictTo(array('json', 'js'));

		$this->status(201);
		$this->add_header('CUSTOM_HEADER', 'TEST');
		return array('test' => 'ok');
	}

	// Test binary json values (this should fail)
	public function get_utf8($ctx) {

		$this->restrictTo(array('json', 'js'));
		return array('status' => 'fail', 'expected' => 'fail', 'invalidUTF8' => pack("H*" ,'c32e') );
	}

	// Get the PHP version on this server
	public function get_php_version() {
		return array(phpinfo());
	}
}