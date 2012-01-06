<?php

include_once('_helpers.php');

class URI {
	
	public $raw 	= '';
	public $parameters 	= array();
	private $type	= '';
	private $options = array();
	
	public function __construct($uri) {
		
		$this->raw = $uri;		
		$uri = (object) parse_url(ltrim($uri, '/'));		
		$this->type = coalesce(strstr($uri->path, '.'), DEFAULT_RES_TYPE);
		$this->path = str_replace($this->type, '', $uri->path);
		$this->parameters = explode('/', $this->path);
		parse_str($_SERVER['QUERY_STRING'], $this->options);
	}
	
	public function type() { return ltrim($this->type, '.'); }
	public function res() { return implode('/', $this->parameters) . $this->type; }
	public function flag($f) { return $this->opt($f) !== NULL; }
	public function opt($k) { return (array_key_exists($k, $this->options)) 
		? $this->options[$k] : NULL ; }
		
	public function component($d) { 
		return coalesce( 
			str_replace('-', '_', reset($this->parameters)), $d ); 
	}
	public function bump() { return array_pop($this->parameters); }
}
class Request {

	public $host;
	public $method;
	public $action;
	public $service;	
	public $uri;
	public $query;
	
	public function __construct() {
	
		// bootstrap request parameters
		$this->uri = new URI($_SERVER['REQUEST_URI']);
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->action = coalesce($this->method, 'get');
		$this->host = $_SERVER['HTTP_HOST'];
		$this->service = strstr($this->host, '.', -1);

		// reset wrapped globals
		$_GET = array();	
	}
	
	public function __toString() { return print_r($this, true); }
}


?>