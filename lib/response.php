<?php

/* A PrestoPHP HTTP response

*/
class Response {
	private $call;
	private $sentHeaders = 0;
	public static $type_handlers = array();
	private static $ver;
	private $codes = array(
			'200' => 'OK',
			'201' => 'Created',
			'202' => 'Accepted',
			'204' => 'No Content', // (NO BODY)
			'205' => 'Reset Content', // (NO BODY)
			'206' => 'Partial Content', // (ADD'L HEADERS)

			'304' => 'Not modified',

			'400' => 'Bad request',
			'401' => 'Auth failed',
			'402' => 'Payment required',
			'403' => 'Forbidden',
			'404' => 'Not found',
			'405' => 'Method not allowed',
			'406' => 'Not acceptable',
			'409' => 'Conflict',
			'410' => 'Gone',
			'412' => 'Precondition failed',
			'415' => 'Unexpected media type',
			'417' => 'Expectation Failed',
			'418' => 'I\'m a little teapot', // (RFC 2324)

			'500' => 'Internal Server Error',
			'501' => 'Not Implemented',
			'503' => 'Service Unavailable',
			'506' => 'Variant Also Negotiates'
	);


	/* Set up the response */
	public function __construct($ctx = null) {
		if ($ctx === null) $ctx = (object) array('res' => 'json');

		$this->call = $ctx;

		// register default type handlers

		self::add_type_handler('application/json', function ($dom) {
			$json = json_encode($dom);
			if (json_last_error() !== JSON_ERROR_NONE) throw new Exception('JSON encoding error #' . json_last_error(), 400);
			print $json;
		} );

		self::add_type_handler('.*\/htm.*', function($dom) { _encode_html($dom); } );

		if (PRESTO_DEBUG) self::add_type_handler('text/plain', function ($dom) { print_r($dom); } );
	}

	/* Register a type handler */
	public static function add_type_handler($type, $encoder_fn, $mapper_fn = null) {
		if (!is_callable($encoder_fn)) throw new Exception('Invalid type handler.', 500);
		if ($mapper_fn !== null && !is_callable($mapper_fn)) throw new Exception('Invalid type mapper.', 500);

		self::$type_handlers[$type] = (object) array('enc' => $encoder_fn, 'map' => $mapper_fn);
	}

	/* Respond to a request */
	public function ok($ctx, $enc = true, $c = 200, $h = null) {
		if (!$this->hdr($c, $h))
			return false; // returns if status does not allow a body

		if ($enc) return self::encode($this->content_type(), $ctx->data);
		else return print $ctx->data;
	}
	/* Respond with a failure */
	public function fail($d, $c = 500) {
		if (!$this->hdr($c)) return false; // no data sent to client
		return self::encode($this->content_type(), $d);
	}

	/* Generate an appropriate HTTP header */
	public function hdr($c = '200', $h = null) {
		$message = array_key_exists($c, $this->codes) ? $this->codes[$c] : 'Internal error';

		if ($this->sentHeaders) return true;
		else $this->sentHeaders = 1;

		$v = defined('SERVICE_VERSION') ? SERVICE_VERSION : PRESTO_VERSION;
		header("HTTP/1.0 {$c} {$message}");
		header(VERSION_HEADER . ': ' . $v);
		header('Cache-Control: no-cache');

		if (in_array($c, array('201', '204'), true))
			return false; // no body allowed

		header('Content-type: ' . $this->content_type());

		if (!empty($this->call->modified))
			header('Last-Modified: '.$this->call->modified);

		// include custom headers
		if ($h) foreach($h as $k => $v) header("$k: $v");

		return true;
	}

	/** Determine the content-type */
	private function content_type() {
		if (!isset($this->call) || empty($this->call->type))
			return 'text/plain';

		if (strpos($this->call->type, '/')) return $this->call->res; // already a content-type

		// map obvious content types
		switch ($this->call->type) {
			case 'html':
			case 'htm':
				return 'text/html';

			default:
				return 'application/' . $this->call->type;
		}
	}

	/* Encode the response using type handlers */
	private static function encode($type, $dom) {
		$h = false;

		// find encoder

		if (array_key_exists($type, self::$type_handlers))
			$h = self::$type_handlers[$type]; // direct mapping
		else {
			foreach (self::$type_handlers as $exp => $handler)
				if (preg_match("#$exp#", $type)) $h = self::$type_handlers[$exp]; // expression mapping
		}

		if (!$h) throw new Exception('Unknown resource type: ' . $type, 500);

		$encode = $h->enc;
		$map = $h->map;
		$encode($dom, $map);
	}

	public function __toString() { return print_r($this, true); }
}
