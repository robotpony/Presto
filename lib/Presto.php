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
	public $req = array();
	public $sess = array();
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
	
	private function dispatch() {
		
		$obj = $this->req->uri->component('error');
		$thing = $this->req->uri->component('');
		$action = $this->req->action;
		
		$method = (strlen($thing)) ? "{$thing}_{$action}" : $action;
		
		$this->call = array(
			'class' => $obj, 'method' => $method, 
			'res' => $this->req->uri->type(), 'params' => $this->req->uri->parameters,
			'exists' => false); 

		if ($obj == 'error') die('Root access not allowed');
		
		if (!method_exists($obj, $method))
			throw new Exception("Can't find $obj->$method()");
		
		$this->call['exists'] = true; 
		
		$o = new $obj($this->req, $this->sess);

		$o->$method($this->call);
	}	
}

?>