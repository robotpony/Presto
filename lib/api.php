<?php

namespace napkinware\presto;

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
		
		if (func_num_args() == 2) throw new \Exception('Code upgrade required (Presto base classes have changed).', 500);
	}
	
	/* Attach to Presto framework */
	public function attach($ctx, $resp, $req) {
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
		if (empty($key)) throw new \Exception('Missing header key (API::add_header).', 500);
		$this->headers[$key] = $value;
	}
	public function headers() { return $this->headers; }

	/* Restrict the valid contentTypes for this API or API route */
	public function restrictTo($types) {
		return $this->supports_contentType($types);
	}

	/* Get a filtered variable (get filter_var_array + exceptions) */
	static function filtered($thing, $rules, $defaults = null) {
		if ($defaults) $thing = array_merge($defaults, (array)$thing);
		$filtered = filter_var_array((array)$thing, $rules);

		if ( $filtered === null )
			throw new \Exception("Invalid or missing parameter(s)", 406);

		if ( $missing = array_filter( $filtered, function($v) { return $v === null; } ) )
			throw new \Exception("Missing parameter(s): " . implode(array_keys($missing), ', '), 406);

		if ( $invalid = array_filter( $filtered, function($v) { return is_bool($v) && $v === FALSE; } ) )
			throw new \Exception("Invalid parameter(s): " . implode(array_keys($invalid), ', '), 406);

		return (object) $filtered;
	}

	private function supports_contentType($t) {
		$in = self::$ctx->class . '::' . self::$ctx->method . '()';
		$type = self::$ctx->type;

		if (!is_array($t)) $t = array($t);
		if (!in_array($type, $t)) throw new \Exception("Unsupported media type '$type' for '$in'.", 415);
	}
}
