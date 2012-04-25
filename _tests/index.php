<?php
include('../lib/presto.php');

$dsn = 'mysql:host=localhost;dbname=test';
$user = 'test';
$password = '12345';
$config = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
); 

try {

	$db = new db($dsn, $user, $password, $config);
	status("Connected to `$dsn`", 'OK');
	
	$results = $db->select('SELECT * FROM Test');
	status("Simple select", 'OK');
	print_r($results);
	
	
} catch (Exception $e) {

	status("Failed to {$e->getMessage()}", 'FAIL');
	print("\n");
	print($e);

}

function status($text, $status) {
	$status = strtoupper($status);
	$c = $status == 'OK' ? '42' : '41';

	print " \033[1;{$c};30m[ {$status} ]\033[0m\t\t$text\n";
}
?>

