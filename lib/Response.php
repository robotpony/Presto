<?php

class Response {

	public function __construct() {
	
	}
	
	public function __toString() { return print_r($this, true); }
}


?>