<?php
require_once('config.php');

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
			'login-redirect' 	=> ''
		);

		if ($settings) $this->cfg = (object) array_merge($settings, $this->cfg);
		else $this->cfg = (object) $this->cfg;
	}
	

	/* Is the request authorized? */
	public function is_authorized() {
	
		try {
		
			if ($this->supports_api_keying())
				if ($this->valid_key()) return true;
				
			if (!isset($_COOKIE[$this->cfg->cookie_name]))
				return false; // not signed in
				
			$this->t = new token($_COOKIE[$this->cfg->cookie_name]);
			
			if (!$this->t->ok()) 
				return $this->redirect('log-in.html?a=token-revoked&m=Invalid&20token'); // invalid auth
			
		} catch (Exception $e) {
			dump($e->getMessage());
			return false;
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
	public function sso($target, $p = null) {
		$params = '';
		if (is_array($p) && count($p)) {
			$params = '?';
			foreach ($p as $k => $v) $params .= "$k=$v&";
		} elseif (is_string($p)) $params = '?'.$p;
			
		return header('Location: '.$target.$params);
		print "Redirecting you to <a href='$target'>another page</a> ...";
		exit;
	}
	/* Save the session */
	public function save($t, $r = false) { // TODO domain, secure, http-only
		$expiry = $r ? 36000 : 600;

		if (empty($this->cfg->cookie_name) 
			|| !setcookie($this->cfg->cookie_name, $t, time() + $expiry, '/'))
			throw new Exception('Cookie monster sad :-(', 500);
	}
	/* Clear the session */
	public function clear() {
		setcookie($this->cfg->cookie_name, '', time() - 1, '/');
	}
	
	// get the current token
	public function token() { return $this->t->ok() ? $this->t->encoded() : false; }
	// get info about the current user
	public function user() { return is_object($this->t) && $this->t->ok() ? 
		$this->t->parts() : false; }
	// can an API key work?
	private function supports_api_keying() { return $this->cfg->api_key_header; }
	// is this a session for an API?
	private function is_api() { return false; }
	// is the API key valid?
	private function valid_key() {
		return ($this->supports_api_keying() && $_SERVER[$this->cfg->api_key_header] === $this->cfg->sso_key);
	}
	

}
