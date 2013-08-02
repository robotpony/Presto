<?php

/* Delgation debugging tests

	* Internal testing route for the Presto delegation
	* Provides insight into Presto delegation for given routes
		
*/
class delegation extends API {

	public function __construct() {
		parent::__construct('presto-example-1');
		// other startup would go here
	}

	/* Gets debug information for routing to this class
		
		This is an example of how a concrete call is routed
	*/
	public function get() {
		$this->restrictTo('json');
		return self::$req->scheme();
	}
}