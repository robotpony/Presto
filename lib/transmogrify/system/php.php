<?php

/* An API for returning information

	Notice that the classname matches the filename, which will match the URLs it serves.

	This class will service requests like:

		example.com/info.json
		example.com/info/some/path.json

	The requests will be mapped to class functions named <method>_<thing>.<resource type>.
*/
class php extends API {

	public function __construct() {
		parent::__construct('presto-example-1');
		// other startup would go here
	}

	// Test binary json values (this should fail)
	public function get_utf8($ctx) {

		$this->restrictTo('json');
		return array('status' => 'fail', 'expected' => 'fail', 'invalidUTF8' => pack("H*" ,'c32e') );
	}

	// Get the PHP info on this server
	public function get_info() {
		$this->restrictTo('html');
		return phpinfo();
	}
}