<?php include_once('_config.php');

include_once(PRESTO_BASE.'/_helpers.php');
include_once(PRESTO_BASE.'/api.php');

/** Presto micro web services framework

*/
class Presto extends REST {
	public $call;
	public static $v; // version
	
	/** Initialize with the request, and start delegation */
	public function __construct($v = '' /* version */) {

		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		set_error_handler(array($this, 'fail'));
		
		self::$v = $v;
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
	
	/* Dispatch requests to classes and class methods */
	private function dispatch() {
		
		try {
	
			$obj = self::$req->uri->component('error' /* default to an error route */);
			$o = new $obj();	
			$action = self::$req->action;	// determines the request action (method)
			$thing = self::$req->uri->thing(); // determine the thing (resource)
	
			// validate that the concept noun is valid
			if (!$o->is_valid_concept($thing))
				$thing = ''; // no thing (resource) available, assume root action
	
			// validate that the content type is supported
			if (!$o->is_valid_contentType(self::$req->uri->type()))
				throw new Exception("Unsupported media type: $action $thing.", 415);

			// build the call pseudo object
			$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;	
			$this->call = (object) array(
				'class' 	=> $obj,
				'method' 	=> $method,
				'res' 		=> self::$req->uri->type(), 
				'params' 	=> self::$req->uri->parameters,
				'exists' 	=> false);			
	
			// build the response object
			self::$resp = new response($this->call, self::$v);
	
			// verify the request
			
			if ($obj == 'error') // disallow root component access
				throw new Exception('Root access not allowed', 403);
			
			if (!method_exists($obj, $method)) // check that the resource is valid
				throw new Exception("Can't find $obj->$method()", 404);	

			$this->call->exists = true; 
			
			self::_trace("Dispatching to $obj :: $method");		
		
			// delegate
			$this->call->data = $o->$method( $this->call, self::$req->body() );
		
			// 
			if (is_object($this->call->data) || is_array($this->call->data))
				return self::$resp->ok($this->call->data, self::$req);
			else
				return $this->call->data;

		} catch (Exception $e) {			
			self::$resp->hdr($e->getCode());
			throw $e;
		}
	}

	/** Presto failures (including many PHP failures) */
	static public function fail($n, $text, $file, $line, $ctx) {
		$call = (object) array('res' => 'json');
		self::$resp = new response($call);

		$details = array(
			'code' => $n,
			'text' => $text, 
			'file' => $file,
			'line' => $line,
			'context' => $ctx
		);

		$codes = array(2 => '404');
		self::$resp->hdr(coalesce(@$codes[$n], '500'));
		
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
		
		print "TRACE: \n\t".implode("\n\t", func_get_args()) . "\n\n";
	}
	
}

?>
