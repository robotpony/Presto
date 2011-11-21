<?php
include(dirname(__FILE__).'/Service.php');

define('PROMPT', '> ');
$api = null;
$options = array(
	'service' => 'http://app.rubrix.local/',
	'extra' => 'v2'
);

// Run a simple Presto shell
function shellinate() {
	print PROMPT;
	
    while ($cmd = fgets(STDIN)) {
    	global $options;
    	global $api;
    	$service = '';

    	
		$cmd = strtok(trim($cmd), ' ');
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
					
					$pairs = explode(',', $value);
					foreach ($pairs as $v) {
						$p = explode('=', $v);
						if (count($p) == 2) $params[$v[0]] = $v[1]; 
						else $params[] = $p;
					}
										
					print $api->$method($p);
				
				} catch (Exception $e) {
					print $e->getCode() . "\n" . $e->getMessage() . "\n";
				}
				
				
			break;
			
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