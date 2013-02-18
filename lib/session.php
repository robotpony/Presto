<?php

/** Simple tokenized sessions

*/
class session {

	private $t;
	private $cfg;

	/* Create a new session

	*/
	public function __construct($serviceID, $settings = null) {

		$this->cfg = array(
			'cookie_name' 		=> SERVICE_COOKIE,
			'token_header'		=> '',
			'service' 			=> $serviceID,
			'api_key_header' 	=> null,
			'sso_key' 			=> null,
			'login_redirect' 	=> '',
			'domain'			=> COOKIE_DOMAIN,
			'secure'			=> SECURED_COOKIE,
			'api_keyed'			=> false,
			'key_auth'			=> null
		);

		if ($settings) $this->cfg = (object) array_merge($this->cfg, $settings);
		else $this->cfg = (object) $this->cfg;

		if (empty($this->cfg->cookie_name) || !isset($this->cfg->domain) || !isset($this->cfg->secure))
			throw new Exception('Missing Session class configuration.', 500);
	}

	/* Is the request authorized? */
	public function is_authorized() {

		try {
			$t = '';

			if ($this->supports_api_keying())
				if ($this->valid_key()) return true;

			if (isset($_COOKIE[$this->cfg->cookie_name])) {
				$t = $_COOKIE[$this->cfg->cookie_name];
			} else {
				if (!empty($this->cfg->token_header) && !isset($_SERVER[$this->cfg->token_header]))					
					throw new Exception('Not authorized (no session cookie).', 401); // not signed in
				elseif (!empty($this->cfg->token_header))
					$t = urldecode($_SERVER[$this->cfg->token_header]);
			}

			$this->t = new auth_token($t);

			if (!$this->t->ok())
				throw new Exception('Invalid authorization token.', 401); // invalid token

		} catch (Exception $e) {
			if ($e->getCode() != 401)
				throw new Exception('Authorization error.', 500, $e);
			else
				throw $e;
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
	public function token() { return isset($this->t) && $this->t->ok() ? $this->t->encoded() : false; }
	// get info about the current user
	public function user() { return is_object($this->t) && $this->t->ok() ?
		$this->t->parts() : false; }
	// can an API key work?
	private function supports_api_keying() { return $this->cfg->api_keyed && $this->cfg->api_key_header && array_key_exists($this->cfg->api_key_header, $_SERVER); }
	// is this a session for an API?
	private function is_api() { return class_exists('API') && class_exists('Presto'); }
	// is the API key valid?
	private function valid_key() { 

		if (!array_key_exists($this->cfg->api_key_header, $_SERVER))
			return false; // no API key
		
		$key = $_SERVER[$this->cfg->api_key_header];

		// check as external key
		return is_callable($this->cfg->key_auth)
			&& call_user_func($this->cfg->key_auth, $key);
	}
}
