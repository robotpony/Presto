<?php

namespace napkinware\presto;

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

	// Render a view
	public static function render($v, $d, $p = '') {
		$v = new View("$p$v", $d);
		return $v->renderer();
	}

	// Class setup
	public function __construct($view = 'index', $data = null) {

		// hook view parameters

		$this->d = array('dom' => $data); // namespaced into "dom"
		$this->f = is_array($view) ? $view : array($view);

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
				$this->v = $f;

				if (!stream_resolve_include_path($this->v))
					throw new \Exception("View {$this->v} ({$this->v}) not found in: ".get_include_path().".", 404);

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
}