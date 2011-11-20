<?php 
/**
	Presto micro web services framework
	(pico REST to $x)
	
	Prototype 2
		
		service
			request		method	path	options
				

*/
if (phpversion() != '5.3.6') { print 'Unsupported version of PHP. (' . phpversion() . ')'; die; };

include_once('_config.php');
include_once('_helpers.php');

class Presto {
	public $req;
	public $resp;
	public $sess;
	public $call;
	
	public function __construct() { 	
		$this->_base = $_SERVER['DOCUMENT_ROOT'];
		
		$this->req = new request();
		
		set_include_path($this->_base);		
		
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
	}

	private function filter() {	
	}
	
	/* Dispatch requests to classes and class methods */
	private function dispatch() {
		
		$obj = $this->req->uri->component('error');
		$thing = $this->req->uri->component('');
		$action = $this->req->action;
		$params;
		
		$o = new $obj();
		
		if (!$o->validConcept($thing)) {
			$params[] = $thing;
			$thing = '';
		}
		
		$method = (strlen($thing)) ? "{$action}_{$thing}" : $action;
		
		$this->call = (object) array(
			'class' => $obj,
			'method' => $method, 
			'res' => $this->req->uri->type(), 
			'params' => array_merge($this->req->uri->parameters, $params),
			'exists' => false); 
			
		$this->resp = new response($this->call);

		if ($obj == 'error')
			throw new Exception('Root access not allowed');
		
		if (!method_exists($obj, $method))
			throw new Exception("Can't find $obj->$method()");	
		
		$this->call->exists = true; 
		
		$call->data = $o->$method($this->call);

		$this->resp->hdr();
		
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
	
}

?>