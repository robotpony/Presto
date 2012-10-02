<?php

/** Simple tokenized sessions

*/
class session {

	private $t;
	private $cfg;

	/* Set up session

	## Parameters

		session name
		extra settings
	*/
	public function __construct($serviceID, $settings = null) {

		$this->cfg = array(
			'cookie_name' 		=> SERVICE_COOKIE,
			'service' 			=> $serviceID,
			'api_key_header' 	=> null,
			'sso_key' 			=> null,
			'login-redirect' 	=> '',
			'domain'			=> COOKIE_DOMAIN,
			'secure'			=> SECURED_COOKIE
		);

		if ($settings) $this->cfg = (object) array_merge($settings, $this->cfg);
		else $this->cfg = (object) $this->cfg;

		if (empty($this->cfg->cookie_name) || !isset($this->cfg->domain) ||
			!isset($this->cfg->secure))
			throw new Exception('Missing Session class configuration.', 500);
	}


	/* Is the request authorized? */
	public function is_authorized() {

		try {

			if ($this->supports_api_keying())
				if ($this->valid_key()) return true;

			if (!isset($_COOKIE[$this->cfg->cookie_name]))
				throw new Exception('Not authorized (no session cookie).', 401); // not signed in

			$this->t = new auth_token($_COOKIE[$this->cfg->cookie_name]);

			if (!$this->t->ok())
				throw new Exception('Invalid authorization token.', 412); // invalid auth

		} catch (Exception $e) {
			throw new Exception('Authorization error.', 500, $e);
		}

		return true; // signed in
	}

	/* Is the client attempting to authorize? */
	public function is_authorizing() {
		return (array_key_exists('email', $_POST) && $_SERVER['REQUEST_METHOD'] == 'POST');
	}

	/* Redirect the client (this seems out of place) -- >> RESPONSE  */
	public function redirect($target) {
		return header('Location: /'.$target);
		print "Redirecting you to <a href='/$target'>another page</a> ...";
		exit;
	}
	/* Redirect the client (this seems out of place) -- >> RESPONSE */
	public function sso_target($target, $p = null) {
		$params = '';
		if (is_array($p) && count($p)) {
			$params = '?';
			foreach ($p as $k => $v) $params .= "$k=$v&";
		} elseif (is_string($p))
			$params = '?'.$p;

		return $target.$params;
	}

	/* Save the session */
	public function save($t, $r = false) { // TODO domain, secure, http-only
		$expiry = $r ? 36000 : 600;

		if (empty($this->cfg->cookie_name)
			|| !setcookie($this->cfg->cookie_name, $t, time() + $expiry, '/',
				$this->cfg->domain, $this->cfg->secure))
			throw new Exception('Cookie monster sad :-(', 500);
	}
	/* Clear the session */
	public function clear() {
		if (!setcookie($this->cfg->cookie_name, '', time() - 1, '/', $this->cfg->domain, $this->cfg->secure))
			throw new Exception('Failed to remove cookie', 500);
	}

	// get the current token
	public function token() { return $this->t->ok() ? $this->t->encoded() : false; }
	// get info about the current user
	public function user() { return is_object($this->t) && $this->t->ok() ?
		$this->t->parts() : false; }
	// can an API key work?
	private function supports_api_keying() { return $this->cfg->api_key_header; }
	// is this a session for an API?
	private function is_api() { return class_exists('API') && class_exists('Presto'); }
	// is the API key valid?
	private function valid_key() {
		return ($this->supports_api_keying() && $_SERVER[$this->cfg->api_key_header] === $this->cfg->sso_key);
	}


}
