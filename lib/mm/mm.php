<? 	header('Content-type: application/json');

	/* A micro form mailer (part of the Presto toolkit)
	
		Copyright (C) 2010-2011 Bruce Alderson	
	*/
	
	// configuration 
	
	$website = 'napkinware.com';
	$sender = 'bruce@napkinware.com';
	$to 	= $sender; 	
	
	
	
	// form POST helper
	function _post($keys) { 
		$out = array();
		foreach ($keys as $k) {
		
			$v = @filter_input(INPUT_POST, $k,
				FILTER_SANITIZE_STRING,
				FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

			$out[$k] = ($v == null) ? '' : $v;
		}
		return $out;
	}
	// API fail helper
	function _api_fail($m = 'API error', $detail = '') {
		print json_encode(array(
			'ok' => 0,
			'title' => $m,
			'body' => $detail
		));
		die;
	}

	
	try {
		
		// get, sanitize inputs, and check		
		
		$user = _post(array('name', 'email', 'company', 'message'));
		
		$user['email'] = filter_var($user['email'], FILTER_VALIDATE_EMAIL);
		
		if (empty($user['email']))
			return _api_fail('Missing or invalid email address');
		if (empty($user['name']))
			return _api_fail('Missing contact name');
		if (empty($user['message']))
			return _api_fail('Missing message');
		
		$from = $user['email'];
		$headers = "From: $sender\r\n" .
		    "Reply-To: $from\r\n" .
		    'X-Mailer: Presto/v1/' . phpversion();
		    
		$message = <<<EMAIL
From    : {$user['name']} <{$user['email']}>
Company : {$user['company']}

{$user['message']}

----
Via $website contact form
EMAIL;
		
		// attempt to send the form
		$sent = mail($to, 
			"[$website] contact request from {$user['name']}",
			$message, 
			$headers, 
			"-f$sender");
		
			
	} catch (Exception $e)  {
		return _api_fail('Internal error', $e->getMessage());	
	}	
	
	// send response
	print json_encode(array(
		'ok' => $sent,
		'title' => 'Thank you!',
		'body' => 'Your request has been sent.',
		'name' => $user['name']
	));	
?>