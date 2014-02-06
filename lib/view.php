<?php

namespace napkinware\presto;
use \Exception;

/* A simple PrestoPHP view

	Usage:

		print new View('login', array( 'user' => 'test-user' ) );

	Often used to generate special output, like HTML or other indirect object->output mappings. Don't use
	this to generate JSON or XML (those are more easily generated with DOM->adapter patterns)

	Usage:

		print napkinware\presto\View::render('login', array( 'user' => 'test-user' ) );

	Returns the rendered text of the view or throws a status code (as a standard PHP exception).

*/
class View {
	private $d;
	private $v;
	private $r;
	private $f;

	// Render a view
	public static function render($v, $d, $p = '') {
		$v = new View($v, $d, $p);
		return $v->renderer();
	}

	// Class setup
	public function __construct($view = 'index', $data = null, $base = null) {

		// hook view parameters

		$this->d = array('dom' => $data, 'view' => $this); // namespaced into "dom"
		$this->f = is_array($view) ? $view : array($view);
		$this->base = $base ? $base : '';

		return $this;
	}

	// Do the rendering
	private function renderer() {
		try {

			// render view and return
			extract($this->d);
			ob_start();

			if (empty($this->f))
				throw new \Exception('You did not specify any view paths.', 501);

			// verify and load multipart view
			foreach ($this->f as $f) {
				$this->v = "{$this->base}$f";

				if (!stream_resolve_include_path($this->v))
					throw new \Exception("View {$this->v} (Not found in: ".get_include_path().".", 404);

				include($this->v);
			}

			$output = ob_get_contents();
			ob_end_clean();
			return $output;

		} catch (\Exception $e) {
			if ($e->getCode() === 404) throw $e;
			throw new \Exception('Failed to render view.', 500, $e);
		}
	}
	
	/* Include a file as a piece of a view. */
	public function load_part($p) {
		try {
		
			extract($this->d);
			
			if (stream_resolve_include_path($p) === false)
				throw new Exception("Template file $p not found in: ". get_include_path() .".", 404);
			
			include($p);

		} catch (\Exception $e) {
			if ($e->getCode() === 404) throw $e;
			throw new \Exception('Failed to render view.', 500, $e);
		}
	}
}
