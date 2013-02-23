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
	public function get($p) {

		$this->restrictTo('json');

		if (count($p) > 1)
			throw new Exception('Too many parameters', 400); // will result in a proper 400 HTTP status

		return array('example' => 'This is some example information'); // will be returned as json, if json is requested
	}

	// Test custom header values
	public function get_header_test($ctx) {
		$this->restrictTo('json');

		$this->status(201);
		$this->add_header('CUSTOM_HEADER', 'TEST');
		return array('test' => 'ok');
	}

	// Test binary json values (this should fail)
	public function get_utf8($ctx) {

		$this->restrictTo('json');
		return array('status' => 'fail', 'expected' => 'fail', 'invalidUTF8' => pack("H*" ,'c32e') );
	}

	// Get the PHP version on this server
	public function get_php_version() {
		return array(phpinfo());
	}
}