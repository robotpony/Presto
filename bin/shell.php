<?php
include(dirname(__FILE__).'/../lib/Service.php');

define('PROMPT', '> ');
$api = null;
$options = array(
	'service' => 'http://dev.rubrix.local/',
	'extra' => 'v2',
	'debug' => 0,
	'log' => 'req'
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
					$pairs = explode(',', $value);
					foreach ($pairs as $v) {
						$p = explode('=', $v);
						if (count($p) == 2) $params[$p[0]] = $p[1]; 
						else $params[] = $p;
					}
					
					// make call
					$call = "{$cmd}_{$method}";
					$data = $api->$call($params);
					print_r($data);
					print "\n";
				
				} catch (Exception $e) {
					print "\n" . $e->getCode() . " : " . $e->getMessage() . "\n";
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

  set service [service]
  [get|post|put|etc] [uri] [data]
	
	
Examples

  set service http://localhost/
  get test.json
	

	
<?php
}
?>
The Presto shell
("help" for some options)

<?php
shellinate();
    
?>