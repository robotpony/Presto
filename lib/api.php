<?php

/* Presto API base class.

	Base your APIs on this class.

*/
class API extends REST {

	private $concepts;
	private $delegates = array();
	private $status = 200;
	private $headers = array();
	public static $version;
	public static $ctx;
	public static $resp;
	public static $req;

	/* Initialization */
	public function __construct($c /* class for introspection */, $v = '' /* version for headers */) {

		self::$version = $v;

		// learn valid REST concepts from class members

		foreach (get_class_methods($c) as $fn) {

			$method = strtok($fn, '_');
			$concept = strtok('');

			if (!empty($concept) && in_array($method, self::$METHODS))
				$this->concepts[] = $concept;
		}
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
		if (empty($key)) throw new Exception('Missing header key (API::add_header).', 500);
		$this->headers[$key] = $value;
	}
	public function headers() { return $this->headers; }

	/* Test if a route refers to a valid concept (member) */
	public function is_valid_concept($c) { return !empty($this->concepts)
		&& in_array($c, $this->concepts); }

	/*	Advanced route mapping

		Adds a mapping between a URI pattern and a callback to delegate to. Used to route complex
		sub delegates, for things like hierarchical resources.
	*/
	public function add_delegate($regex, $delegateFn) {

		// check for conflicts in previously added delegates.
		if (array_key_exists($regex, $this->delegates))
			throw new Exception("URI delegate already exists: pattern collision for '$regex'", 500);

		// preflight (and compile + cache) the regex ... errors handled by Presto.
		preg_match($regex, '');
		$this->delegates[$regex] = $delegateFn;
	}

	/* Do delegation for hierarchical sub-routes

		Provides internal delegation to registered callbacks.

		Throws if delegation fails.
	*/
	public function delegate($ctx, $data = null) {
		if (empty($this->delegates) || empty($ctx) || empty($ctx->params))
			throw new Exception('Unserviceable internal delegation attempt.', 501);

		$path = implode('/', array_slice($ctx->params, 1));
		foreach ($this->delegates as $p => $d) {
			if (preg_match($p, $path)) {
				if (empty($data)) return $this->$d($ctx);
				else return $this->$d($ctx, $data);
			}
		}
		throw new Exception("Bad request. No sub method exists for resource $path", 404);
	}

	/* Restrict the valid contentTypes for this API or API route

	*/
	public function restrictTo($types) {
		return $this->validate_contentType($types);
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

	private function validate_contentType($t) {
		$in = self::$ctx->class . '::' . self::$ctx->method . '()';
		$res = self::$ctx->res;

		if (!is_array($t)) $t = array($t);
		if (!in_array($res, $t)) throw new Exception("Unsupported media type $res for $in.", 415);
	}
}
