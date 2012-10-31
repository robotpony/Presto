<?php

/* A basic view helper
*/
class View {
	private $d;
	private $v;
	private $r;
	private $f;
	public static $root;
	
	public function __construct($view = 'index', $data = null) {
		
		// hook view parameters
		
		$this->d = array('dom' => $data); // namespaced into "dom"
		$this->f = $view;
	}
	
	public function render() {		
		try {
		
			// verify and load view
			if (!is_dir(self::$root)) self::$root = realpath(__DIR__) . '/';	
			if (empty(self::$root)) self::$root = PRESTO_BASE . '../_views';
			$this->v = self::$root."/{$this->f}.php";
		
			if (!is_file($this->v)) 
				throw new Exception("View ({$this->f}) not found in '".self::$root."'.", 501);
		
			// render view and return
			extract($this->d);
			ob_start();
			include($this->v);
			$output = ob_get_contents();
			ob_end_clean();			
			return $output;
			
			// note that this could be faster (if called post-header creation in Presto)
			
		} catch (Exception $e) {
			throw new Exception('Failed to render view.', 500, $e);
		}
	}
}