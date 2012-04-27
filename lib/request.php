<?php

include_once('_config.php');
include_once('_helpers.php');

/* A URI wrapper/decoder

	Treats a URI as a resource, decoding it into useful parts.
*/
class URI {
	
	public $raw 		= '';
	public $parameters 	= array();
	
	private $type		= '';
	private $path		= '';
	private $options 	= array();

	/* Decode a URI into parts */	
	public function __construct($uri) {

		$this->raw = $uri;
		$uri = (object) parse_url(ltrim($uri, '/'));

		if (empty($uri->path)) $uri->path = '';
		
		$this->type = pathinfo($uri->path, PATHINFO_EXTENSION);
		
		$uri->path = str_replace('.'.$this->type, '', $uri->path);
		$this->path = str_replace($this->type, '', $uri->path);

		$this->parameters = explode('/', $this->path);

		if (!empty($uri->query)) parse_str($uri->query, $this->options);
	}
	
	// get the resource type
	public function type() { return $this->type; }
	// get the resource extension
	public function ext() { return !empty($this->type) ? '.'.$this->type : ''; }
	// get the resource name
	public function res() { return implode('/', $this->parameters) . $this->ext(); }
	// get a URI flag (returns true/false)
	public function flag($f) { return $this->opt($f) !== NULL; }
	// get a URI option (a GET parameter)
	public function opt($k) { return (array_key_exists($k, $this->options)) 
		? $this->options[$k] : NULL ; }
	// get all of the URI optins as an object
	public function options() { return (object) $this->options; }
	// get the thing that this URI refers to
	public function thing() { return !empty($this->parameters[1]) ? str_replace('-', '_', $this->parameters[1]): ''; }
	// get the component that this URI refers to
	public function component($d) { 
		return coalesce( 
			str_replace('-', '_', reset($this->parameters)), $d ); 
	}
	// bump a parameter off this URI
	public function bump() { return array_pop($this->parameters); }
	
}

/** A RESTful request

Decodes and makes available various portions of a request, including:

* URI
* POST
* encoded bodies

*/
class Request {

	public $host;
	public $method;
	public $action;
	public $service;	
	public $uri;
	public $query;
	public $post;

	/* Set up  a request object (from PHP builtins) */	
	public function __construct() {
		
		// Use the URI from either .htaccess routing or the raw request
		$uri = $_SERVER['REQUEST_URI'];		
		if (array_key_exists('r', $_GET)) {
			$type = array_key_exists('t', $_GET) ? $_GET['t'] : 'json';
			$uri = $_GET['r'].'.'.$type;
		}
		
		// bootstrap request parameters
		$this->uri = new URI($uri);
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->action = coalesce($this->method, 'get');
		$this->host = $_SERVER['HTTP_HOST'];
		$this->service = strstr($this->host, '.', -1);

		// reset wrapped globals
		$_GET = array();
		
		
	}
	
	/** Get a post value (or values)
	
	Relies on PHP's built in filtering mechanics. These are a reliable, thourough set
	of filters. Learn them. Use them.
		
	$f
	: Either the parameter to get, or the set of parameters and filters (based
		on the filter_input* APIs)
	
	Returns the value or values requested. Caches values for debugging and other 
		
	See parameter definitions for:
	
		http://php.net/manual/en/function.filter-input-array.php
		http://www.php.net/manual/en/function.filter-input.php
	*/
	public function post($f = null) {
		if (is_array($f)) {
			$this->post = filter_input_array(INPUT_POST, $f);
		} elseif (is_string($f)) {
			$this->post[$f] = filter_input(INPUT_POST, $f);
			return $this->post[$f];
		} else {
			foreach ($_POST as $k => $v)
				$this->post($k);
		}
		
		if ( $this->post )
			return (object)  $this->post;
		
		throw new Exception('Missing or invalid POST parameter', 400);
	}

	/** Get a request body 
	
	Currently hardcoded to interpret as a JSON body. Add other types in the future, 
	based on the request.
		
	*/
	public function body() {
		$json = false;		

		if ( ($body = @file_get_contents('php://input')) ) {
			if (empty($body)) return $json; // no data, not an error
			
			if ( ! ($json = json_decode($body)) ) {
				
				$errors = array(
					JSON_ERROR_NONE => 'No errors.',
					JSON_ERROR_DEPTH  => 'Maximum stack depth exceeded',
					JSON_ERROR_STATE_MISMATCH  => 'Underflow or the modes mismatch',
					JSON_ERROR_CTRL_CHAR  => 'Unexpected control character found',
					JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
					JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
				);
			
				throw new Exception('Invalid request payload. ' . $errors[json_last_error()], 500);
			}
		}		
		
		return $json;
	}
	
	// dump the object to a string
	public function __toString() { return print_r($this, true); }
}
?>