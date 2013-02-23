<?php

/* Presto setup testing API

*/
class httpd extends API {

	public function __construct() {
		parent::__construct(get_class());

		// other startup here
	}

	// info.json (root get request)
	public function get($p, $o, $b, $t) {

		$this->restrictTo('json');
		return array('example' => 'HTTPD test');
	}
	

	// Test custom header values
	public function get_headers($p, $o, $b, $t) {
		$this->restrictTo('json');

		$this->status(201);
		$this->add_header('CUSTOM_HEADER', 'TEST');
		return array('test' => 'ok');
	}	

}