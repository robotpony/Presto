<?php
/* A PrestoPHP HTTP request object (and direct dependencies) */

include_once('_config.php');
include_once('_helpers.php');

/* A REST request

	Decodes and makes available various portions of a request, including: the URI, and the encoded request body.
*/
class Request {

	public $uri;		// source URI
	public $path;		// path of source URI (minus any query and fragment)

	public $container;	// target API container
	public $route;		// target API route
	public $type;		// resource type (based on URI)

	public $host;		// hostname
	public $tld;		// top level domain name
	public $scheme;		// request scheme (http, https, etc.)
	public $method;		// requested method
	public $action;		// presto's target action
	public $service;	// service name
	public $query;		// query parameters
	public $get;		// get parameters
	public $post;		// post parameters
	public $options;	// query options
	public $referer;	// the likely referring URI
	public $refererPath;	// path of the likely referring URI (referer minus any query and fragment)

	/* Set up	a request object (from PHP builtins) */
	public function __construct($r = null, $t = null, $c = null) {

		$this->uri = $_SERVER['REQUEST_URI'];
		$this->path = parse_url($this->uri, PHP_URL_PATH);

		// set up basic delegation concepts (via params or htaccess)

		$this->container = presto_lib::_get('c', $c);
		$this->route = presto_lib::_get('r', $r);
		$this->type = presto_lib::_c(presto_lib::_get('t', $t), 'json');
		$params = $this->params();

		if (!array_key_exists('r', $_GET) || !array_key_exists('t', $_GET) || !array_key_exists('c', $_GET))
			presto_lib::_trace("Rewrite delegation setup for {$this->uri} is missing.", json_encode($_GET));

		unset($_GET['t']); unset($_GET['r']); unset($_GET['c']); // pop routing parameters

		// setup request parameters

		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$this->action = presto_lib::_c($this->method, 'get'); // default to GET
		$this->host = $_SERVER['HTTP_HOST'];

		$this->referer = _server('HTTP_REFERER', '');
		$this->refererPath = parse_url($this->referer, PHP_URL_PATH);

		$this->service = strstr($this->host, '.', -1);
		$this->tld = pathinfo($this->host, PATHINFO_EXTENSION);
		$this->scheme = _server_has('HTTPS', 'on')
			|| _server_has('HTTP_X_FORWARDED_PROTO', 'https')
			|| _server_has('HTTP_X_FORWARDED_SSL', 'on') ? 'https' : 'http';

		$this->options = $_GET;
		$_GET = array(); // discourage use of $_GET
	}

	/* Get the request mapping scheme */
	public function scheme() {
		$p = explode('/', presto_lib::_cleanup($this->route));
		$class = presto_lib::_at($p, 0, '');
		$res = presto_lib::_at($p, 1, '');
		$file = empty($this->container) ? "$class.php" : "$this->container/$class.php";
		$method = empty($res) ? $this->method : $this->method . '_' . $res;
		$preflight = "{$method}_model";

		return (object) array(
			'container' => presto_lib::_cleanup($this->container),
			'class' 	=> presto_lib::_cleanup($class),
			'file'		=> str_replace('/.', '.', $file),
			'resource' 	=> $res,
			'type'		=> $this->type,
			'action'	=> $this->method,
			'method' 	=> presto_lib::_cleanup($method),
			'preflight'	=> presto_lib::_cleanup($preflight),
			'params' 	=> $this->params(),
			'options' 	=> $this->options,
			'referer'	=> $this->referer
		);
	}
	public function params() {
		$p = explode('/', presto_lib::_cleanup($this->route));
		$p = array_slice($p, 2, count($p));

		return $p;
	}

	/* Determine if this is an internal request
		Not intended for secure uses (informational only), as the referer field can be spoofed.
	*/
	public function isInternalRequest() {
		$via = parse_url($this->referer, PHP_URL_HOST);
		return ($this->host === $via);
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
	public function get($f = null, $throw = true) {

		if ( $this->get = $this->filter(INPUT_GET, $f) )
			return (object)	$this->get;

		if ($throw)
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
	public function post($f = null, $throw = true) {

		if ( $this->post = $this->filter(INPUT_POST, $f) )
			return (object)	$this->post;

		if ($throw)
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

			switch ($this->content_type($this->type)) {
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
						throw new Exception('Invalid JSON request payload - ' . $errors[json_last_error()] . ' - ' . $body, 400);
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

/* Request helpers - TODO - move these */

// safely get a server variable with a default
function _server($k, $d = false) { return array_key_exists($k, $_SERVER) ? $_SERVER[$k] : $d; }
// safely check to see if a server variable is a particular value
function _server_has($k, $v, $d = false) { return array_key_exists($k, $_SERVER) ? $_SERVER[$k] == $v : $d; }
