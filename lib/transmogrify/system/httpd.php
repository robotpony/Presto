<?php

/* Presto setup testing API

*/
class httpd extends API {

	public function __construct() {
		parent::__construct(get_class());

		// other startup here
	}

	// info.json (root get request)
	public function get($ctx) {

		$this->restrictTo('json');
		return array('example' => 'HTTPD test');
	}

}