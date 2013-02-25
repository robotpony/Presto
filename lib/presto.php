<?php include_once('_config.php');

include_once(PRESTO_BASE.'/_helpers.php');
include_once(PRESTO_BASE.'/autoloader.php');
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


	/* Dispatch requests to classes and class methods */
	private function dispatch() {

		try {

			$this->call = self::$req->scheme();

			$action = self::$req->action;	// the request action (method)
			$obj = $this->call->class;
			$method = $this->call->method;
			$type = self::$req->type;

			$res = $this->call->resource; // the root resource

			presto_lib::_trace('REQUEST', "[{$this->call->file}] $obj::$method ({$this->call->type})", 
				json_encode($this->call->params), json_encode($this->call->options));

			// Create an an instance of the API subclass (autoloaded)
			
			autoload_delegate($this->call);
			
			if (!class_exists($obj))
				throw new Exception("API class not found for $obj::$method", 404);
				
			$o = new $obj();

			// Start the response setup
			
			self::$resp = new response($this->call);

			// Verify the request

			if ($obj == 'error') // disallow root component access
				throw new Exception('Root access not allowed', 403);

			if (!method_exists($obj, $this->call->method)) // valid route?
				throw new Exception("Can't find $obj->$method", 404);

			$this->call->exists = true;

			// Perform the actual sub delegation
			
			$o->attach( $this->call, self::$resp, self::$req );
			$this->call->data = $o->$method( $this->call->params, $this->call->options, self::$req->body(), $this->call->type );

			// Produce a response for the client
			
			presto_lib::_trace( PRESTO_TRACE_KEY, json_encode(Presto::trace_info()) );

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

		$details = array(
			'status' => $status,
			'code' => $n,
			'error' => $text,
			'file' => $file,
			'line' => $line,
			'ctx' => $ctx
		);
		
		if (PRESTO_TRACE) $details[PRESTO_TRACE_KEY] = Presto::trace_info();

		// build the resulting error object
		$details = json_encode( (object) $details);
		
		error_log('FATAL', $status, $details);
		self::$resp->hdr($status);
		print $details;
		die;
	}

	// Get trace info for a call
	static public function trace_info() {
		return array(
			'routing_scheme' => self::$req->scheme(),
			'request' => self::$req->uri,
			'version' => PRESTO_VERSION
		);
				
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
}
