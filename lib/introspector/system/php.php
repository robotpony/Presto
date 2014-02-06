<?php

/* PHP compatibility tests

	* Internal testing route for the Presto install tools
*/
class php extends API {

	public function __construct() {
		parent::__construct('presto-example-1');
		// other startup would go here
	}

	// Test binary json values (this should fail)
	public function get_utf8($p, $o, $b, $t) {

		$this->restrictTo('json');
		return array('status' => 'fail', 'expected' => 'fail', 'invalidUTF8' => pack("H*" ,'c32e') );
	}

	// Get the PHP info on this server
	public function get_info($p, $o, $b, $t) {
		$this->restrictTo('html');
		return phpinfo();
	}
	
	// Gets php versioning and compatibility
	public function get_version($p, $o, $b, $t) {

		$this->restrictTo('json');

		$ver = explode('.', phpversion());
		$ok = ($ver[0] >= '5' && $ver[1] >= 3);
		
		$libs = array(
			'curl' => array( 'is_compatible' => (boolean) function_exists('curl_init'), 'version' => curl_version() ),
			'json*' => array('is_compatible' => (boolean) function_exists('json_encode'), 'version' => '' )
		);

		return array(
			'is_compatible' => (boolean) $ok, 
			'version' => $ver,
			'libs' => $libs
		);
	}
}