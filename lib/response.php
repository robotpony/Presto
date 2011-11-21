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
	
	
	public function __construct($ctx = null) {
		$this->call = $ctx;	
	}
	
	public function send() {
	
	
	}
	public function __toString() { return print_r($this, true); }
	
	
	// output the API header
	function hdr($c = '200') {
		if ($this->sentHeaders) return;

		$this->sentHeaders = 1;
		
		header("HTTP/1.0 {$c} {$this->codes[$c]}");
		header(VERSION_HEADER . ': ' . API_VERSION);
		
		if (in_array($c, array('201', '204'), true)) return false; 

		$type = (!isset($this->call) || empty($this->call->res)) ?
			'text/plain'
			: 'application/' . $this->call->res;

		header('Content-type: ' . $type);

		if (!empty($this->call->modified))
			header('Last-Modified: '.$this->call->modified);
				
		header('Cache-Control: no-cache');
		
		if (empty($this->call->data)) return;
	
		if ($this->call->res !== 'xml') return;
	print <<<XML
<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet href="/styles/xml.css" type="text/css"?>

XML;
	}
	
}


?>