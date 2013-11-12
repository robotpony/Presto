<?php

/* Presto API base class.

	Base your APIs on this class.

*/
class API extends REST {

	private $status = 200;
	private $headers = array();
	public static $version;
	public static $ctx;
	public static $resp;
	public static $req;

	/* Initialization */
	public function __construct($v = '' /* version for headers */) {
		self::$version = $v;
		
		if (func_num_args() == 2) throw new Exception('Code upgrade required (Presto base classes have changed).', 500);
	}
	
	/* Attach to Presto framework */
	public static function attach($ctx, $resp, $req) {
		self::$ctx = $ctx;
		self::$resp = $resp;
		self::$req = $req;
	}
	/* Set or get the HTTP status */
	public function status($s = null) {
		if ($s !== null) $this->status = $s;
		return $this->status;
	}
	/* Get and set custom headers */
	public function add_header($key, $value) {
		if (empty($key)) throw new Exception('Missing header key (API::add_header).', 500);
		$this->headers[$key] = $value;
	}
	public function headers() { return $this->headers; }

	/* Restrict the valid contentTypes for this API or API route */
	public function restrictTo($types) {
		return $this->supports_contentType($types);
	}

	/* Set necessary CORS headers for this API route.
	
		* Sets `Access-Control-Allow-Origin` appropriately with respect to referer (necessary for `Access-Control-Allow-Credentials`)
		* Sets `Access-Control-Allow-Credentials: true`: allow `rx-auth` cookie to be included in request (authentication)
		* Sets `Access-Control-Allow-Methods: GET`: only GETs supported for now
		* sets some other useful default headers

	*/
	public function allowCrossOrigin() {

		if (empty($_SERVER['HTTP_ORIGIN']))
			return; // This is not a CORS request
			
		$this->add_header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
		$this->add_header('Access-Control-Allow-Credentials', 'true');
		$this->add_header('Access-Control-Allow-Methods', 'GET');
		$this->add_header('Access-Control-Allow-Headers', 'Content-Type,Accept');
		$this->add_header('Access-Control-Max-Age', '10');
	}

	/* Get a filtered variable (get filter_var_array + exceptions) */
	static function filtered($thing, $rules, $defaults = null) {
		if ($defaults) $thing = array_merge($defaults, (array)$thing);
		$filtered = filter_var_array((array)$thing, $rules);

		if ( $filtered === null )
			throw new Exception("Invalid or missing parameter(s)", 406);

		if ( $missing = array_filter( $filtered, function($v) { return $v === null; } ) )
			throw new Exception("Missing parameter(s): " . implode(array_keys($missing), ', '), 406);

		if ( $invalid = array_filter( $filtered, function($v) { return is_bool($v) && $v === FALSE; } ) )
			throw new Exception("Invalid parameter(s): " . implode(array_keys($invalid), ', '), 406);

		return (object) $filtered;
	}

	private function supports_contentType($t) {
		$in = self::$ctx->class . '::' . self::$ctx->method . '()';
		$type = self::$ctx->type;

		if (!is_array($t)) $t = array($t);
		if (!in_array($type, $t)) throw new Exception("Unsupported media type '$type' for '$in'.", 415);
	}
}
