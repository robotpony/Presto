<?php
/* A PrestoPHP HTTP request object (and direct dependencies) */

include_once('_config.php');
include_once('_helpers.php');

/* A REST request

	Decodes and makes available various portions of a request, including: the URI, and the encoded request body.
*/
class Request {

	public $uri;

	public $container;
	public $route;
	public $type;

	public $host;
	public $method;
	public $action;
	public $service;
	public $query;
	public $get;
	public $post;
	public $options;

	/* Set up	a request object (from PHP builtins) */
	public function __construct($r = null, $t = null, $c = null) {

		$this->uri = $_SERVER['REQUEST_URI'];

		// set up basic delegation concepts (via params or htaccess)

		$this->container = presto_lib::_get('c', $c);
		$this->route = presto_lib::_get('r', $r);
		$this->type = presto_lib::_c(presto_lib::_get('t', $t), 'json');
		$params = $this->params();

		if (empty($this->route) || empty($this->type))
			throw new Exception("Missing rewrite delegation setup for $uri.", 500);

		unset($_GET['t']); unset($_GET['r']); unset($_GET['c']); // pop routing parameters

		// setup request parameters

		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->action = presto_lib::_c($this->method, 'get'); // default to GET
		$this->host = $_SERVER['HTTP_HOST'];
		$this->service = strstr($this->host, '.', -1);

		$this->options = $_GET;
		$_GET = array(); // discourage use of $_GET
	}

	/* Get the request mapping scheme */
	public function scheme() {
		$p = explode('/', presto_lib::$this->route);
		$class = presto_lib::_at($p, 0, '');
		$res = presto_lib::_at($p, 1, '');
		$file = empty($this->container) ? "$class.php" : "$this->container/$class.php";
		$method = empty($res) ? $this->method : $this->method . '_' . $res;
		
		return (object) array(
			'container' => presto_lib::_cleanup($this->container),
			'class' 	=> presto_lib::_cleanup($class),
			'file'		=> $file,
			'resource' 	=> $res,
			'type'		=> $this->type,
			'action'	=> $this->method,
			'method' 	=> presto_lib::_cleanup($method),
			'params' 	=> $this->params(),
			'options' 	=> $this->options
		);
	}
	public function params() {
		$p = explode('/', presto_lib::$this->route);
		$p = array_slice($p, 2, count($p));

		return $p;
	}
	/* Get a GET value (or values)

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

	/* Get a post value (or values)

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

	/* Get a request body

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

			switch ($this->content_type()) {
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
	/* Determine the content type of the call

		Usesg $_SERVER['CONTENT_TYPE'] if possible.

		If not default to the extension initially parsed off of the request URI.
	*/
	public function content_type($ext) {

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

	// dump the object to a string
	public function __toString() { return print_r($this, true); }
}
