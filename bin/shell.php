<?php
include(dirname(__FILE__).'/../lib/Service.php');

define('PROMPT', "\n> ");
$api = null;

$options = array_merge(
	array(
		'service' => 'http://localhost/',
		'extra' => '',
		'debug' => 0,
		'log' => 'req'
	),
	getopt('', array(
		'service:',
		'extra:',
		'debug',
		'log:')
	)
);

// Run a simple Presto shell
function shellinate() {
	print PROMPT;
	
	$last = '';
    while ($cmd = fgets(STDIN)) {
    	global $options;
    	global $api;
    	$service = '';
    	$cmd = trim($cmd);   	
    	
    	if (empty($cmd))
    		/* reuse last command (and do not log) */
	   		$cmd = $last;
	   	else {
    	
	    	$last = $cmd;
	    	
    		// log command (handy for testing)
	    	$history = fopen('hist.log', "a+");	
			fwrite($history, $last."\n");
	    	fclose($history);	    	
	    }	    	
    	
		$cmd = strtok($cmd, ' ');
		$method = strtok(' ');
		$value = strtok('');
    		
		switch ($cmd) {
			case 'help': 
				show_help();
			break;
			
			case 'debug':
			case 'dump':

				print "\n";
				
				switch ($method) {
				
					case 'call':
						print_r($api->info());
						print_r($api->payload());
					break;
					
					case '':
					default:
						print "Don't know how to dump '$method'.\n";	
				}
				
			break;
			
			case 'show':
				print "\n";
				if (in_array($method, array('', '*', 'all')) || !array_key_exists($method, $options))
					foreach ($options as $k => $v) { print "\t$k :\t $v\n"; }
				else
					print "$method = $options[$method]\n";
					
				print "\n";
			break;
			
			case 'set':
				if (strlen($method)) {
					$options[$method] = $value;
					print "Set $method to $value\n";
				}
			break;
			
			case 'get':
			case 'post':
			case 'put':
			case 'delete':
			case 'options':
			case 'head':
			
				try {
					
					if (!isset($api)) $api = new Service( $options );
					
					// grab parameters (parse if they look like key=value,...
					
					$params = array();
					if (strlen($value) && in_array($value[0], array('[', '{'))) {
						// attempt to decode as JSON
						$params = json_decode($value);
						if (empty($params))	throw new Exception('Invalid JSON parameters : ' . $value);
					} else {
						// attempt to decode as key/value set
						$pairs = explode(',', $value);
						foreach ($pairs as $v) {
							$p = explode('=', $v);
							if (count($p) == 2) $params[$p[0]] = $p[1]; 
							else $params[] = $p;
						}
					}
					
					$method = trim($method, "/ \n"); // clean up the method string
										
					// make call
					$call = "{$cmd}_{$method}";
					print_r(array($call, $params)); // TODO shell bug here post_history/x/y/z
					$data = $api->$call($params);
					var_dump($data);
				
				} catch (Exception $e) {
					print $e->getCode() . " : " . $e->getMessage() . "\n\n";
				}
				
				
			break;
			
			case 'quit':
			case 'end':
			case 'die':
				die("Done.\n");	
				
			default:
				print "Not sure how to '$cmd'.\n";
			
			
		}
		print PROMPT;			
    }
}
function show_help() {
?>

Commands

  show [option]
  set [option] [value]
  dump [thing]
  [get|post|put|etc] [uri] [data]
	
Examples

  get test.json
  
  # show the details of the last call
  dump call
	

	
<?php
}
?>
The Presto shell
("help" for some options)

Connecting to <?= $options['service'] ?> ...
Connected.

<?php
shellinate();    
?>