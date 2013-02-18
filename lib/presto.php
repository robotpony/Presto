<?php include_once('_config.php');

include_once(PRESTO_BASE.'/_helpers.php');
include_once(PRESTO_BASE.'/api.php');

/* Presto micro web services framework

*/
class Presto extends REST {
	public $call;

	/* Initialize with the request, and start delegation */
	public function __construct() {

		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		set_error_handler(array($this, 'fail'));

		self::$req = new request();

		try {
			$this->dispatch(); // dispatch to loaded class->member based on $req
		} catch (Exception $e) {
			throw $e; // errors are handled by delegator
		}
	}

	private static function autoload_explicit($class) {
		// First look in the base directory for the web app
		$class_file = strtolower($class) . ".php";
		if (file_exists($class_file))
			return require_once($class_file);

		throw new Exception("API `$class` not found.", 404);
	}

	/* Dispatch requests to classes and class methods */
	private function dispatch() {

		try {

			$obj = self::$req->uri->component('error' /* default to an error route */);
			$action = self::$req->action;	// determines the request action (method)
			$thing = self::$req->uri->thing(); // determine the thing (resource)

			// Create an an instance of the API subclass (autoloaded)
			
			self::autoload_explicit($obj);
			if (!class_exists($obj)) throw new Exception("API not found for $obj", 404);
			$o = new $obj();

			// Calidate that the concept (noun) is valid
			
			if (!$o->is_valid_concept($thing))
				$thing = ''; // no concept available, assume root resource

			// Build the call pseudo object

			$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;
			$this->call = (object) array(
				'class' 	=> $obj,
				'method' 	=> $method,
				'res' 		=> self::$req->uri->type(),
				'params' 	=> self::$req->uri->parameters,
				'options'	=> self::$req->uri->options,
				'exists' 	=> false
			);

			// Start the response setup
			
			self::$resp = new response($this->call);

			// Verify the request

			if ($obj == 'error') // disallow root component access
				throw new Exception('Root access not allowed', 403);

			if (!method_exists($obj, $method)) // check that the resource is valid
				throw new Exception("Can't find $obj->$method()", 404);

			$this->call->exists = true;
			self::_trace("Dispatching to $obj :: $method");

			// Perform the actual sub delegation
			
			$o->attach( $this->call, self::$resp, self::$req );
			$this->call->data = $o->$method( $this->call, self::$req->body() );

			// Produce a response for the client
			
			$encode = (is_object($this->call->data) || is_array($this->call->data));
			return self::$resp->ok( $this->call, $encode, $o->status(), $o->headers() );

		} catch (Exception $e) {
			if (self::$resp === null) self::$resp = new response();

			self::$resp->hdr($e->getCode());
			throw $e;
		}
	}

	/* Handle PrestoPHP failures (including all catchable PHP failures) */
	static public function fail($n, $text, $file, $line, $ctx) {

		// set up pseudo call and response
		if (self::$resp === null)
			self::$resp = new response($ctx);

		// generate useful HTTP status
		switch ($n) {
			case 2: $status = 400; break;
			default: $status = 500;
		}

		// build the resulting error object
		$details = (object) array(
			'status' => $status,
			'code' => $n,
			'error' => $text,
			'file' => $file,
			'line' => $line,
			'ctx' => $ctx
		);

		self::$resp->hdr($status);
		print json_encode($details);
		die;
	}

	/** Debugging dump of Presto delegator */
	public function __toString() { return print_r($this, true); }
}

/** REST base class

	Constants and base methods
*/
class REST {

	public static $METHODS = array(
		'get', 'put', 'post', 'delete', 'options', 'head');
	public static $TYPES = array(
		'json', 'xml');

	public static $req;
	public static $resp;
	public static $sess;

	public static function _trace() {
		if (PRESTO_DEBUG == 0) return;
		error_log("TRACE: ".implode("\n\t", func_get_args()));
	}

}
