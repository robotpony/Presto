<?php 

class API {

	public static $req;
	public static $resp; 
	public static $sess; 

	public function __construct($req, $sess, $resp) {	
		self::$req = $req;
		self::$resp = $resp;
		self::$sess = $sess;
	}
	
	public function status() {}
}

?>