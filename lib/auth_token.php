<?php 
/** Authentication tokens

Useful as a GET parameter, header value, or cookie value. Tokens are both encryped and
validated with a hash value, and can contain a few Kb of key/value pairs.

## Notes

1. You must define:

	* TOKEN_HASH_SECRET
	* TOKEN_ENCRYPTION, TOKEN_SIGNING_KEY, and SIGNING_INIT before
	  working with a token
  
## Yet TODO

	* support for W rollover
	* expose R parameters (and define structure) ... pass off to second class?

*/
class auth_token {
	private $t;
	private $p;
	private $hash;
	private $roles = array();
	
	/* Create a token from parts or a token string */
	public function __construct($v) {

		if (!defined('TOKEN_HASH_SECRET') || !defined('TOKEN_ENCRYPTION') 	
				|| !defined('TOKEN_SIGNING_KEY') || !defined('SIGNING_INIT'))
			throw new Exception('Missing token configuration', 500);
			
		if (is_array($v)) { 
			// build from parameters			
			$this->build($v, false /* force creation of check + timestamp */ );
			$this->encrypt();			
		} elseif (is_string($v)) {
			// build from encrypted string
			$this->t = $v;
			$this->decrypt();			
		} else
			throw new Exception('Missing auth_token or token parts.', 500);
	}
	
	/* Get the encoded token */
	public function encoded() { $this->encrypt(); return $this->t; }
	
	/* Get the token parts */
	public function parts() {return $this->p;}
	
	/* Is this token valid? */
	public function ok() {
		return count($this->p) && !empty($this->p->h) 
				&& $this->p->h == $this->hash;
	}
	
	/* ---------------------------- internals ---------------------------- */
	
	/* Get the checked parts as a string (for calculating checksums) */	
	private function checked_parts() {
		if (empty($this->p)) throw new Exception('Token is not initialized.', 500);
		$elements = array($this->p->name, $this->p->email, $this->p->id, $this->p->acct, 
						TOKEN_HASH_SECRET, $this->p->t);	
		
		foreach ($elements as &$e) $e = urlencode($e);
		return implode('', $elements);
	}

	/* Get a token as an encoded URI string */	
	private function uri() {
		if (empty($this->p)) throw new Exception('Token is not initialized.', 500);
		$uri = '';
		foreach ($this->p as $k => $v) $uri .= $k.'='.urlencode($v).'&';
		return $uri;				
	}
	
	/* Build the token object from parts 
		
		A strict build checks that all parts are provided
		
		Returns false if the token build has failed.
	*/
	private function build($p, $strict = true) {

		// apply defaults
		$this->set($p['t'], time(), $strict);
		$this->set($p['w'], 60, $strict);

		// check for required elements
		foreach (array('name', 'id', 'acct', 'a', 'c', 's') as $k)
			if (empty($p[$k])) // missing a required token element
				throw new Exception('Invalid credentials, missing: '.$k, 401);
				
		if (empty($p['email']) && empty($p['key']))
			throw new Exception('Invalid credentials, missing email and key, at least one is required', 401);

		foreach ($p as $k => &$v) $v = urldecode($v); // remove URI encoding
				
		$this->p = (object) $p; // objectize for convinience

		// extract capabilities+roles
		if (strlen($this->p->c)) {
			$roles = explode(',', $this->p->c);
			foreach ($roles as $tuple) {
				$cap = explode('/', $tuple);
				if (count($cap) !== 2) throw new Exception('Invalid token capabilities.', 401);
				$role = $cap[0];
				parse_str($cap[1], $list);
				$this->roles[$role] = array_keys($list);
			}
		}
		
		// lastly, apply a default value to the CRC (if needed/possible)
		$this->set($this->p->h, sha1($this->checked_parts()), $strict);		
		$this->hash = sha1($this->checked_parts());
				
		return $this->hash == $this->p->h;
	}
	
	/* Assign a token value a default (if possible) */ 
	private function set(&$v, $d, $strict = true) {
		if (!empty($v))
			return;
			
		if (!$strict) $v = $d; 
		else throw new Exception('Missing required check field.', 401);
	}
	
	/* Encrypt a token from parts */
	private function encrypt() {	
		$this->t = openssl_encrypt($this->uri(), 
			TOKEN_ENCRYPTION, TOKEN_SIGNING_KEY, false, SIGNING_INIT);
			
		return $this->t;
	}
	
	/* Decrypt a token into parts (thows on errors) */
	private function decrypt() {		
		if (empty($this->t))
			throw new Exception('Token is empty.', 401);

		$t = openssl_decrypt($this->t, 
			TOKEN_ENCRYPTION, TOKEN_SIGNING_KEY, false, SIGNING_INIT);

		if (empty($t))
			throw new Exception('Token seems invalid.', 401);

		parse_str( $t, $p );

		if (empty($p))
			throw new Exception('Token format invalid.', 401);
		
		if (!$this->build( $p ))
			throw new Exception('Token integrity check failed.', 401);	
			
		return $this->p;
	}

}
