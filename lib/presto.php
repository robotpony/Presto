<?php include_once('_config.php');

include_once(PRESTO_BASE.'/_helpers.php');
include_once(PRESTO_BASE.'/api.php');

/**
 * Automatically load classes that aren't included.
 *
 * @param string $class (Required) The classname to load.
 * @return boolean Whether or not the file was successfully loaded.
 */
function presto_autoloader($class) {
	// First look in the base directory for the web app
	$class_file = strtolower($class) . ".php";
	if (file_exists($class_file)) {
		require_once($class_file);
		return true;
	}
	// Next look in the Presto library directory
	$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
	$lib_file = $path . $class_file;
	if (file_exists($lib_file)) {
		require_once($lib_file);
		return true;
	}
	// We can't find it so we let other autoloaders try
	return false;
}

// Register the autoloader.
spl_autoload_register('presto_autoloader');

/** Presto micro web services framework

*/
class Presto extends REST {
	public $call;
	
	/** Initialize with the request, and start delegation */
	public function __construct() {

		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		set_error_handler(array($this, 'fail'));

		self::$req = new request();

		try {
			$this->filter(); // request filtering
			$this->dispatch(); // dispatch to loaded class->member based on $req	
		} catch (Exception $e) {
			throw $e; // errors are handled by delegator
		}
	}
	
	/** Apply request filters */
	private function filter() {	
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
				
			// Create an an instance of the API subclass
			//	(autoloads based on the path)
			self::autoload_explicit($obj);
			$o = new $obj(); 

			// validate that the concept noun is valid
			if (!$o->is_valid_concept($thing))
				$thing = ''; // no thing (resource) available, assume root action

			// build the call pseudo object
			$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;	
			$this->call = (object) array(
				'class' 	=> $obj,
				'method' 	=> $method,
				'res' 		=> self::$req->uri->type(), 
				'params' 	=> self::$req->uri->parameters,
				'exists' 	=> false);			
	
			// build the response object
			self::$resp = new response($this->call, $o::$version);
				
			// verify the request
			
			if ($obj == 'error') // disallow root component access
				throw new Exception('Root access not allowed', 403);
			
			if (!method_exists($obj, $method)) // check that the resource is valid
				throw new Exception("Can't find $obj->$method()", 404);	

			$this->call->exists = true; 
			
			self::_trace("Dispatching to $obj :: $method");	
		
			// delegate
			$o->attach( $this->call, self::$resp, self::$req );
			$this->call->data = $o->$method( $this->call, self::$req->body() );
		
			// 
			if (is_object($this->call->data) || is_array($this->call->data))
				return self::$resp->ok($this->call, self::$req);
			else
				return $this->call->data;

		} catch (Exception $e) {
			if (self::$resp === null)
				self::$resp = new response();
				
			self::$resp->hdr($e->getCode());
			throw $e;
		}
	}

	/** Presto failures (including many PHP failures) */
	static public function fail($n, $text, $file, $line, $ctx) {

		// set up pseudo call and response
		if (self::$resp === null)
			self::$resp = new response($call);
		
		// generate useful HTTP status
		switch ($n) {
			case 2: $status = 404; break;
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
	}

	/** Debugging dump of Presto delegator */
	public function __toString() { return print_r($this, true); }
}

/** REST base class

	Handy constants and base methods
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

?>
