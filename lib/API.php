<?php 

class API {

	public static $req;
	public static $sess; 

	public function __construct($r,$s) {	
		self::$req = $r;
		self::$sess = $s;
	}
	
	public function status() {}
}

?>