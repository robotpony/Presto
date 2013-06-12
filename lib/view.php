<?php

/* A simple PrestoPHP view

	Usage:
	
		print new View('login', array( 'user' => 'test-user' ) );
	
	Returns the rendered text of the view or throws a status code (as a standard PHP exception).

*/
class View {
	private $d;
	private $v;
	private $r;
	private $f;

	public static function htmlize($v, $d) {
		$v = new View($v, $d);
		return $v->render();	
	}
	
	public function __construct($view = 'index', $data = null) {

		// hook view parameters

		$this->d = array('dom' => $data); // namespaced into "dom"
		$this->f = $view;
		
		return $this;
	}

	public function render() {
		try {
			// verify and load view

			$this->v = "{$this->f}.php";

			if (!stream_resolve_include_path($this->v))
				throw new Exception("View {$this->v} ({$this->v}) not found in: ".get_include_path().".", 501);

			// render view and return

			extract($this->d);
			ob_start();
			include($this->v);
			$output = ob_get_contents();
			ob_end_clean();
			return $output;

		} catch (Exception $e) {
			throw new Exception('Failed to render view.', 500, $e);
		}
	}
}