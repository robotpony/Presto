<?php
/* Convenience representation of fully parsed URI */
class URI {

	public $raw;
	public $parsed;
	
	public function __construct($raw) {
		// validate
		$this->raw = filter_var($raw, FILTER_VALIDATE_URL);
		if (!$this->raw) throw new Exception("Invalid URL: '$raw'", 400);
		
		// parse url
		$this->parsed = parse_url($this->raw);
		// further parse query string if necessary
		if (isset($this->parsed['query'])) parse_str($this->parsed['query'], $this->parsed['query']);
	}
}
