<?php
include('../lib/presto.php');



try {
	simple_database_tests();
	
	simple_service_tests();
	
} catch (Exception $e) {

	status("Failed with: {$e->getMessage()}", 'FAIL');
	print("\n");
	print($e);

}


function simple_database_tests() {
	$dsn = 'mysql:host=localhost;dbname=test';
	$user = 'test';
	$password = '12345';
	$config = array(
	    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	); 
	
	$db = new db($dsn, $user, $password, $config);
	status("Connected to `$dsn`", 'OK');
	
	$r = $db->select('SELECT * FROM Test');
	status("Simple select", 'OK');
	result($r);
}

function simple_service_tests() {
	$config = array(
		'service' 	=> 'https://api.twitter.com/',
		'extra' 	=> '1',
		'debug' 	=> 0,
		'referer' 	=> 'presto/test-script',
		'agent'		=> 'presto/1.0'
	);
	
	$service = new Service($config);
	
	status("Created test service", 'OK');
	//https://api.twitter.com/1/help/test.json	
	$data = $service->get_help('test.json');
	status("Simple GET request", 'OK');
	result($data);
}
function status($text, $status) {
	$status = strtoupper($status);
	$c = $status == 'OK' ? '42' : '41';

	print " \033[1;{$c};30m[ {$status} ]\033[0m\t\t$text\n";
}
function result($t) { ?>

----

<?= var_dump( $t ) ?>

----

<?php }
?>

