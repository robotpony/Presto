<?php
include('../lib/presto.php');

/* PRESTO tests 

*/

try {
	database_tests();
	
	service_tests();
	
	simple_view_tests();
	
} catch (Exception $e) {

	status("Failed with: {$e->getCode()} - {$e->getMessage()}", 'FAIL');
	print("\n");
	print($e);

}

/* Test view helper(s) */
function simple_view_tests() {
	View::$root = realpath(__DIR__) . '/';
	$v = new View('test-view', array('name' => 'test'));
	status("Created simple view", 'OK');
	
}

/* Test PDO and wrapper(s) */
function database_tests() {
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

/* Test service class */
function service_tests() {
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


/* Display status (with some console highlighting) */
function status($text, $status) {
	$status = strtoupper($status);
	$c = $status == 'OK' ? '42' : '41';

	print " \033[1;{$c};30m[ {$status} ]\033[0m\t\t$text\n";
}
/* Display detailed results */
function result($t) { ?>

----

<?= var_dump( $t ) ?>

----

<?php }
?>

