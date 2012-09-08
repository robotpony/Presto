<?php

/** Service (API) abstraction

	A helper for calling remote service APIs with a natural calling sequence.
	Calls are automatically translated into HTTP requests based on a RESTful
	pattern, with smart interpretation of parameters and returned payloads.
	
	Without customization, the library will guess at service calls based on
	the pattern:
	
		$s->post_user_json(array('a' => 'val'));
		     1    2     3   4
		     
		1. The HTTP method (get, put, post, delete)
		2. The root level object(s)
		3. The format (json, xml, etc.), optional
		4. The payload (optional, adaptive)

*/
class Service {

	private $urlBuilderFn;
	private $options;		// service options
	private $call;			// call parameters and results

	public static $METHODS = array(
		'get', 'put', 'post', 'delete', 'options', 'head');
	public static $TYPES = array(
		'json', 'xml');
	
	/* Initialize a specific service */
	public function __construct($options, $urlBuilder = NULL) {
	
		if (!function_exists('curl_init'))
			throw new Exception('cURL required by the Presto::Service lib.');
		 
		// set up the service options
		$this->options = (object)array_merge(
			array(
				'server' 	=> '',
				'username' 	=> '',
				'referer' 	=> '',
				'agent' 	=> 'PHP/Presto - Using cURL',
				'type' 		=> 'x-www-form-urlencoded',
				'debug' 	=> NULL,
				'extra'		=> '',
				'log'		=> '',
				'glue'		=> '/',
				'headers'	=> array()
			),
			$options
		);

		$this->call = (object) array(
			'uri'		=> '',
			'params'	=> array(),
			'args'		=> array(),
			'method' 	=> 'get',
			'cookie'	=> '',
			'type'		=> $this->options->type,
			'ext'		=> ''
		);
		
		// set up the default URL builder
		$this->urlBuilderFn = isset($urlBuilder) ? $urlBuilder 
			: function(&$fn, &$o, &$call) {
		 		if (empty($fn)) return false; 	
		 		return "{$o->service}{$o->extra}/{$fn}{$call->id}{$call->ext}"; 
		 	};
	
	}
	
	/* Map service calls to service requests */
	public function __call($fn, $args) {

		// setup call object
		$this->call->ext = '';
		$this->call->type = $this->options->type;
		$this->call->args = $args;
		
		// build arguments
		$this->popArgs($args);					
		// determine function call name
		$fn = $this->parseCall($fn);
	
		// build a url from the call parameters
		$urlFn = $this->urlBuilderFn;
		$this->call->uri = $urlFn($fn, $this->options, $this->call);
	
		// set up the result object
		$this->result = (object) array(
			'body'		=> '',
			'data'	=> array()
		);	

		$this->debug(__FUNCTION__, func_get_args()); // trace

		// make the actual request
	    return $this->request();
	}
	// process the call arguments	
	private function popArgs($args) {
	
		$this->call->params = array();
		$this->call->id = '';
		
		if (empty($args)) return;
		
		foreach ($args as $arg) {
			switch (gettype($arg)) {
				case 'object':
				case 'array':
					$this->call->params = $arg; break;
				
				case 'NULL':
				case 'unknown type':
					break;
					
				default:
					$parts = explode('.', $arg);
					$t = end($parts);
					if (in_array($t, Service::$TYPES)) {
						$this->type(array_pop($parts));
						$arg = str_replace(".$t", '', $arg);
					}
	
					$this->call->id .= "/$arg";
			}
		}

	}
	// parse the call into a useful request
	private function parseCall($fn) {
		
		// parse the fn call
		$parts = explode('_', $fn);
		
		$t = end($parts);
		$m = reset($parts);

		// validate the method
		if (in_array($m, Service::$METHODS))
			$this->call->method = array_shift($parts);

		// validate the type
		if (in_array($t, Service::$TYPES))
			$this->type(array_pop($parts));

		$path = implode('_', $parts); // reassemble the remaining path components

		return $path;
	}	
	// perform the http request
	private function request() {

		$c = curl_init();
		$this->call->headers = $this->options->headers;
		$this->call->info = null;
		
		// set up options specific to each HTTP method
		switch ($this->call->method) {
		
			case 'get':
			break;
				
			case 'post':
				curl_setopt($c, CURLOPT_POST, 1);
				curl_setopt($c, CURLOPT_POSTFIELDS, $this->params());
				$this->call->headers[] = $this->contentType();
				break;
				
			case 'put':
			case 'options':
				curl_setopt($c, CURLOPT_POST, 1);
				curl_setopt($c, CURLOPT_POSTFIELDS, $this->params());
				$this->call->headers[] = $this->contentType();
			break;					

			case 'head':
			case 'delete':
				curl_setopt($c, CURLOPT_CUSTOMREQUEST, 
					strtoupper($this->call->method));
			break;
				
			default:
				throw new Exception('Unsupported HTTP method ' 
					+ $this->call->method);
		}

		// set other HTTP otions
		curl_setopt_array($c, array(
			CURLOPT_URL 			=> $this->call->uri,
			CURLOPT_RETURNTRANSFER	=> 1,
			CURLOPT_TIMEOUT 		=> 100,
			CURLOPT_FOLLOWLOCATION 	=> 1,
			CURLOPT_VERBOSE	 		=> $this->options->debug == 1,
			CURLOPT_COOKIE			=> $this->call->cookie,
			CURLOPT_COOKIESESSION	=> 1,
			CURLOPT_HTTPHEADER 		=> $this->call->headers,
			CURLOPT_REFERER 		=> $this->options->referer,
			CURLOPT_USERAGENT 		=> $this->options->agent,
			
			CURLOPT_HEADERFUNCTION 	=> array($this, 'header')
		));

		$this->log('req', "{$this->call->method}: {$this->call->uri}");

		$this->result->body = curl_exec($c);
		$this->result->uri = $this->call->uri;
		$this->call->info = (object)curl_getinfo($c);

		$this->log('data', $this->result->body);

		$this->parseResults();
						
		if ($this->result->error = curl_error($c))
		    throw new Exception($this->result->error);

		curl_close($c);
				
		if ($this->result->data === false)
			throw new Exception("Data error: {$this->call->method} {$this->call->uri}", $this->call->info->http_code);

		if ($this->call->info->http_code != 200) {
			$dump = ($this->options->debug) ? print_r($this->result, true) 
				: print_r($this->result->data, true);
			throw new Exception("HTTP error\n{$this->call->method} {$this->call->uri}\n\n$dump\n" , $this->call->info->http_code);
		}
		
		return $this->data();
	}

	// Get the call data (raw or processed)
	private function data() { return !empty($this->result->data) ? $this->result->data : $this->result->body; }
	public function code() { return $this->call->info->http_code; }
	public function payload() { return $this->result->body; }
	public function info() { return $this->call; }
	// get the details of the last request
	public function details() { return print "{$this->call->method} {$this->call->uri}{$this->call->id}"; }
	public function opt($k,$v) { $this->options->$k = $v; }
	public function addHeader($k, $v) { $this->options->headers[] = "$k: $v"; }
	public function setCookie($k,$v) { 
		$this->call->cookie .= (empty($this->call->cookie) ? '' : '; ') . $k.'='.urlencode($v); 
	}
	public function cookie() { return $this->call->cookie; }
	public function type($t = null) {	
		if (!empty($t)) {
			$this->call->type = $t;
			$this->call->ext = ".$t";
		}		
		return $this->call->type;
	}
	
	
	// Store the response headers
	private function header($h, $line) {
	
		if (preg_match('/^([^:]+):(.*)$/', $line, $parts)) {
			
			$hdr = trim($parts[1]);
			$v = trim($parts[2]);
						
			switch (strtolower($hdr)) {
				case 'set-cookie':
					$this->call->cookie = $v;
				break;
				default:
					$this->result->header[ $hdr ] = $v;	
			}
		}
		
		return strlen($line);
	}
	
	// Process the results 
	private	function parseResults() {
	
		if (empty($this->result->body)) return false;
		
		switch ($this->call->info->content_type) {

			case 'application/json':
			case 'json':
				$this->result->data = json_decode($this->result->body);
			break;
			
			case 'xml':
			case 'application/xml':
				libxml_use_internal_errors(true);
					
				if (!($this->result->data = simplexml_load_string($this->result->body))) {
					$detail = '';
				    foreach(libxml_get_errors() as $e) {
				    	$detail .= $e->message . "\n" 
				    	. $this->result->body . "\n";
				    }
				    throw new Exception("XML parse error:\n" . $detail, '500');
				}
				
			break;
			
			default:
				$this->result->data = NULL;
				return false;
		}
		return false;
	}
		
	// render the parameters for the request
	private function params() {

		if (empty($this->call->params)) return NULL;
		
		switch ($this->call->type) {
			case 'json':
				$d = json_encode($this->call->params);
				break;
			
			case '':
			case 'x-www-form-urlencoded':
				$d = http_build_query($this->call->params);
				break;
			
			/* TODO: xml encoding for send payload not handled */
			case 'xml':
			default:
				throw new Exception('Unknown data format for send ' 
					. $this->call->type);			
		}
		
		return $d;
	}
	
	// Generate the content-type header
	private function contentType() { 
		return "Content-type: application/{$this->call->type}"; 
	}
	
	private function debug($fn, $args) {
		if ($this->options->debug < 1) return; 
		
		print "$fn "
			. print_r($args, true) . "\n" 
			. print_r(array($this->call, $this->options), true)."\n";	
	}

	private function log($thing, $text) {
		if ($thing != $this->options->log) return;
		
		print "\n$text\n\n";
	}
}

