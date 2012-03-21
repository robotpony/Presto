<?php

class Response {
	private $call;
	private $sentHeaders = 0;
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
	
	/** Set up the response class
	
	*/	
	public function __construct($ctx = null) {
		$this->call = $ctx;	
	}
	
	/** Respond to a request positively 
	
	*/	
	public function ok($d) {

		if (!$this->hdr()) return false; // no data sent to client
		
		switch ($this->call->res) {
			case 'json': 
				print json_encode($d); break;

			case 'htm': 
			case 'html': 
				encode_html($d); break;
			
			default: throw new Exception('Unknown resource type: ' . $this->call->res, 500);
		}
		
		return true;
	}
	public function __toString() { return print_r($this, true); }
	
	
	// output the API header
	function hdr($c = '200') {
		if ($this->sentHeaders) return;

		$this->sentHeaders = 1;
		
		header("HTTP/1.0 {$c} {$this->codes[$c]}");
		header(VERSION_HEADER . ': ' . API_VERSION);
		header('Cache-Control: no-cache');
				
		if (in_array($c, array('201', '204'), true))
			return false; // no body allowed

		header('Content-type: ' . $this->content_type());

		if (!empty($this->call->modified))
			header('Last-Modified: '.$this->call->modified);

		return true;
	}
	
	/***/
	function content_type() {
		if (!isset($this->call) || empty($this->call->res))
			return 'text/plain';
		
		switch ($this->call->res) {
			case 'html':
			case 'htm':
				return 'text/html';
			
			default:
				return 'application/' . $this->call->res;
		}
	}
	
}

/* */
function encode_html($d) {
	if (is_string($d))
		print $d;
	elseif (is_array($d)) {
		foreach ($d as $k => &$v) {
			if (empty($k) || is_numeric($k))
				encode_html($v);
			else {
				print "<$k>\n\t";
				encode_html($v);
				print "</$k>\n";
			}
		}
	}
			
}

?>