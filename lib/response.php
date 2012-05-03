<?php

class Response {
	private $call;
	private $sentHeaders = 0;
	private static $type_handlers = array();
	private static $ver;
	private $codes = array(
			'200' => 'OK',
			'201' => 'Created',
			'202' => 'Accepted',
			'204' => 'No Content', // (NO BODY)
			
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
	public function __construct($ctx = null, $ver = '') {
		$this->call = $ctx;
		self::$ver = $ver;
	
		// register default type handlers
		self::add_type_handler('application/json', function ($dom) { print json_encode($dom); } );
		self::add_type_handler('.*\/htm.*', function ($dom) { encode_html($dom); } );
		if (PRESTO_DEBUG) self::add_type_handler('text/plain', function ($dom) { print_r($dom); } );
	}
	
	/* Register a type handler */
	public static function add_type_handler($type, $encoder_fn, $mapper_fn = null) {
		if (!is_callable($encoder_fn)) throw new Exception('Invalid type handler.', 500);
		if ($mapper_fn !== null && !is_callable($mapper_fn)) throw new Exception('Invalid type mapper.', 500);
			
		self::$type_handlers[$type] = (object) array('enc' => $encoder_fn, 'map' => $mapper_fn);
	}
	
	/* Respond to a request */
	public function ok($d) {
		if (!$this->hdr()) return false; // no data sent to client
		return self::encode($this->content_type(), $d);
	}
		
	/* Generate an appropriate HTTP header */
	public function hdr($c = '200') {
		if ($this->sentHeaders) return;

		$this->sentHeaders = 1;
		
		header("HTTP/1.0 {$c} {$this->codes[$c]}");
		header(VERSION_HEADER . ': ' . self::$ver);
		header('Cache-Control: no-cache');
				
		if (in_array($c, array('201', '204'), true))
			return false; // no body allowed

		header('Content-type: ' . $this->content_type());

		if (!empty($this->call->modified))
			header('Last-Modified: '.$this->call->modified);

		return true;
	}
	
	/** Determine the content-type */
	private function content_type() {
		if (!isset($this->call) || empty($this->call->res))
			return 'text/plain';
		
		if (strpos($this->call->res, '/')) return $this->call->res; // already a content-type
		
		// map obvious content types (should be an array?)
		switch ($this->call->res) {
			case 'html':
			case 'htm':
				return 'text/html';
			
			default:
				return 'application/' . $this->call->res;
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
		$encode($dom, 'root', $h->map);
	}
	
	public function __toString() { return print_r($this, true); }	
}

/* Simple HTML encoder */
function encode_html($node) {
	static $d = -1;
	
	$indent = str_repeat("\t", $d);	
	
	if (is_string($node)) return print "\n$indent$node";	
	else if (!is_array($node)) return;
	
	$d++;
	foreach ($node as $k => &$v) {
		if (empty($k) || is_numeric($k)) return encode_html($v);
		
		print "\n$indent<$k>";
		encode_html($v);
		print "\n$indent</$k>";
	}
			
}

?>