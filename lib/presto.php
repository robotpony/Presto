<?php 
/**
	Presto micro web services framework
	(pico REST to $x)
	
	Prototype 2
		
		service
			request		method	path	options
*/

include_once('_config.php');
include_once(PRESTO_BASE.'/_helpers.php');
include_once(PRESTO_BASE.'/api.php');

class Presto extends REST {
	public $call;
	
	public function __construct() { 	
		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		
		set_error_handler(array($this, 'fail'));
		
		self::$req = new request();

		try {

			$this->filter();
			$this->dispatch();
			
		} catch (Exception $e) {
			throw $e;
		}
	}
	
	public function __toString() { return print_r($this, true); }
	
	private function filter() {	
		// TODO
	}
	
	/* Dispatch requests to classes and class methods */
	private function dispatch() {
		
		$obj = self::$req->uri->component('error');
		$o = new $obj();

		$action = self::$req->action;
		$thing = self::$req->uri->thing();

		if (!$o->validConcept($thing))
			$thing = ''; // don't use param as concept

		$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;

		$this->call = (object) array(
			'class' => $obj,
			'method' => $method,
			'res' => self::$req->uri->type(), 
			'params' => self::$req->uri->parameters,
			'exists' => false);			

		self::$resp = new response($this->call);

		if ($obj == 'error')
			throw new Exception('Root access not allowed', 403);
		
		if (!method_exists($obj, $method))
			throw new Exception("Can't find $obj->$method()", 404);	
		
		$this->call->exists = true; 
		
		self::_trace("Dispatching to $obj :: $method");
		
		try {
		
			$this->call->data = $o->$method( $this->call, self::$req->body() );
			
		} catch (Exception $e) {			
			
			self::$resp->hdr($e->getCode());
			throw $e;
		}
	
		if (is_object($this->call->data) || is_array($this->call->data))
			return self::$resp->ok($this->call->data, self::$req);
		else
			print $this->call->data;
			
		return true;
	}

	
	static public function fail($n, $text, $file, $line, $ctx) {
		self::$resp = new response();
		$codes = array(2 => '404');	

		self::$resp->hdr(coalesce(@$codes[$n], '500'));

		$extra = !empty($ctx) ? "\n\nParameters:\n" . print_r($ctx, true) : '';
		die("#$n\n$text\n$file:$line$extra\n");
	
	}
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
