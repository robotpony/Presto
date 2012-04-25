<?php

/* A basic view helper
*/
class View {
	private $d;
	private $v;
	private $r;
	public static $root;
	
	function __construct($view = 'index', $data = null) {
		if ($data !== null) $this->d = $data;
		
		if (empty(self::$root)) self::$root = PRESTO_BASE . '../_views';
		if (!is_dir(self::$root)) self::$root = realpath(__DIR__) . '/';	
		$this->v = self::$root."$view.php";
		
		if (!is_file($this->v)) 
			throw new Exception("View '$view' not found in '".self::$root."'.", 501);
		
		$this->display();
	}
	
	function display() {
		// TODO: hook presto constants in? other constants?
		
		try {
			extract($this->d);
			require($this->v);
		} catch (Exception $e) {
			print "Failed to show view."; // should this rethrow?	
		}
	}
}