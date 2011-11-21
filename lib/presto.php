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

class Presto extends REST {
	public $call;
	
	public function __construct() { 	
		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		
		self::$req = new request();
		
		try {

			$this->filter();
			$this->authenticate();
			$this->dispatch();
			
		} catch (Exception $e) {
			dump('ERROR', $e);
		}
	}
	
	public function __toString() { return print_r($this, true); }
	
	private function authenticate() {
		// TODO
	}

	private function filter() {	
		// TODO
	}
	
	/* Dispatch requests to classes and class methods */
	private function dispatch() {
		
		$obj = self::$req->uri->component('error');
		$o = new $obj();

		$action = self::$req->action;
		$thing = self::$req->uri->component('');

		if (!$o->validConcept($thing)) {
			self::$req->uri->parameters[] = $thing;	
			$thing = '';
		}

		$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;
		
		$this->call = (object) array(
			'class' => $obj,
			'method' => $method, 
			'res' => self::$req->uri->type(), 
			'params' => self::$req->uri->parameters,
			'exists' => false); 
			

		self::$resp = new response($this->call);

		if ($obj == 'error')
			throw new Exception('Root access not allowed');
		
		if (!method_exists($obj, $method))
			throw new Exception("Can't find $obj->$method()");	
		
		$this->call->exists = true; 
		
		self::_trace("Dispatching to $obj :: $method");
		
		try {
		
			$call->data = $o->$method($this->call);
			
		} catch (Exception $e) {			
			
			self::$resp->hdr($e->getCode());
			print $e->getMessage() . "\n";
			
			return false;
		}
	
		// basic output (TODO: move)
		self::$resp->hdr();
		
		// TODO - setup header response items (content-type, etc.)
		
		if (is_object($call->data) || is_array($call->data))
			print json_encode($call->data);
		else
			print $call->data;
			
		return true;
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
