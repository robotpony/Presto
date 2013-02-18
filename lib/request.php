<?php
/* A PrestoPHP HTTP request object (and direct dependencies) */

include_once('_config.php');
include_once('_helpers.php');

/* A URI decoder

	Treats a URI as a resource, decoding it into useful parts.
*/
class URI {

	public $raw 		= '';
	public $parameters 	= array();
	public $options 	= array();
	private $type		= ''; 		/* Response type implied by request extension. */
	private $payloadType = ''; 		/* Content-type of request payload. */
	private $path		= '';

	/* Decode a URI into parts */
	public function __construct($uri) {

		$this->raw = $uri;
		$uri = (object) parse_url(ltrim($uri, '/'));

		if (empty($uri->path)) $uri->path = '';

		$this->type = pathinfo($uri->path, PATHINFO_EXTENSION);

		$uri->path = str_replace('.'.$this->type, '', $uri->path);
		$this->path = str_replace($this->type, '', $uri->path);

		$this->parameters = explode('/', $this->path);

		$this->options = $_GET;

		$this->payloadType = $this->content_type($this->type);
	}

	// get the resource type
	public function type() { return $this->type; }
	// is the resource a folder?
	public function isFolder() { return empty($this->path) || substr($this->path, -1) === '/'; }
	// get the resource full path
	public function path() { return empty($this->path) ? '/' : $this->path; }
	// get the resource type of the request payload
	public function payloadType() { return $this->payloadType; }
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
		return presto_lib::coalesce(
			str_replace('-', '_', reset($this->parameters)), $d );
	}
	// bump a parameter off this URI
	public function bump() { return array_pop($this->parameters); }

	/*
		Helper: determine the content type of the call using $_SERVER['CONTENT_TYPE'] if possible.
		If not default to the extension initially parsed off of the request URI.
	*/
	private function content_type($ext) {

		if (empty($_SERVER['CONTENT_TYPE'])) return $ext;

		$ct = strtolower($_SERVER['CONTENT_TYPE']);
		$ct = explode(';', $ct);

		foreach ($ct as $v) {
			$candidate = trim($v);

			switch ($candidate) {
				case 'text/html':
					return 'html';

				case 'application/json':
					return 'json';

				default:
					return $ext;
			}
		}
	}

}

/** A REST request

	Decodes and makes available various portions of a request, including: the URI, and the encoded request body.
*/
class Request {

	public $host;
	public $method;
	public $action;
	public $service;
	public $uri;
	public $query;
	public $get;
	public $post;

	/* Set up	a request object (from PHP builtins) */
	public function __construct($r = null, $t = null) {

		// Use the URI from either .htaccess routing or the raw request
		$uri = $_SERVER['REQUEST_URI'];

		// Extract route and type from delegation
		if (isset($r)) $_GET['r'] = $r; if (isset($t)) $_GET['t'] = $t; // override via ctor

		if (!array_key_exists('r', $_GET) || !array_key_exists('t', $_GET))
			throw new Exception('Missing rewrite delegation setup.', 500);

		$type = array_key_exists('t', $_GET) ? $_GET['t'] : 'json';
		$uri = $_GET['r'].'.'.$type;
		unset($_GET['t']);
		unset($_GET['r']);

		// bootstrap request parameters
		$this->uri = new URI($uri);
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->action = presto_lib::coalesce($this->method, 'get');
		$this->host = $_SERVER['HTTP_HOST'];
		$this->service = strstr($this->host, '.', -1);

		// reset wrapped globals
		$_GET = array();


	}

	/** Get a GET value (or values)

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
	public function get($f = null) {

		if ( $this->get = $this->filter(INPUT_GET, $f) )
			return (object)	$this->get;

		throw new Exception('Missing or invalid GET parameter', 400);
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

		if ( $this->post = $this->filter(INPUT_POST, $f) )
			return (object)	$this->post;

		throw new Exception('Missing or invalid POST parameter', 400);
	}

	private function filter($type, $f = null) {

		$r = null;
		$in = $type === INPUT_POST ? $_POST : $_GET;

		if (is_array($f)) {
			$r = filter_input_array($type, $f);
		} elseif (is_string($f)) {
			$r[$f] = filter_input($type, $f);
			return $r[$f];
		} else {
			foreach ($in as $k => $v)
				$r($k);
		}

		if ( $r )
			return (object)	$r;

		return false;
	}

	/** Get a request body

	Currently supports:
	
	* 'application/json'
	* 'application/xml' or 'text/xml'
	* 'application/x-www-form-urlencoded'
	
	Assumes JSON if no content type is specified.
	
	Notes:
	
	* A JSON body is returned as the result of a call to json_decode.
	* An XML body is returned as a SimpleXMLElement object.
	* An application/x-www-form-urlencoded is returned as a string
	* Any other content type throws a 400 exception.

	*/
	public function body() {
		$decoded_body = false;
		if ( ($body = @file_get_contents('php://input')) ) {

			if (empty($body)) return $decoded_body; // no data, not an error

			switch ($this->uri->payloadType()) {
				case 'json':

					if ( ! ($decoded_body = json_decode($body)) ) {

						$errors = array(
							JSON_ERROR_NONE => 'No errors.',
							JSON_ERROR_DEPTH	 => 'Maximum stack depth exceeded',
							JSON_ERROR_STATE_MISMATCH  => 'Underflow or the modes mismatch',
							JSON_ERROR_CTRL_CHAR	 => 'Unexpected control character found',
							JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
							JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
						);
						throw new Exception('Invalid JSON request payload. ' . $errors[json_last_error()], 400);
					}

				break;

				case 'xml':

					$decoded_body = simplexml_load_string($body);
					if ( $decoded_body === false )
						throw new Exception("Invalid XML request payload.", 400);
				break;


				default:

					$decoded_body = $body;
					if ( $decoded_body === false )
						throw new Exception("Invalid form request payload.", 400);
			}

		}

		return $decoded_body;
	}

	// dump the object to a string
	public function __toString() { return print_r($this, true); }
}
