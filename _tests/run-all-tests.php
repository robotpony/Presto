<?php
include('../lib/presto.php');

/* PRESTO tests

*/

try {
//	response_tests();
	response_complex_types_tests();
//	service_tests();
//	simple_view_tests();
//	database_tests();
	
} catch (Exception $e) {

	status("Failed with: {$e->getCode()} - {$e->getMessage()}", 'FAIL');
	print("\n");
	print($e);

}

function response_complex_types_tests() {
	test("Response - complex types tests");

	$dom = <<<JSON
{"chapters" : [
	{"title": "This is chapter 1",
		"id": 101,
		"content" : "This is some text",
		"ideas" : ["This is a list of things", "And another item"]
	},
	{"title": "This is chapter 2",
		"id": 102,
		"content" : "This is some more text",
		"ideas" : ["This is a list of things", "And another item"]
	}
] }
JSON;

	$dom = json_decode($dom, true);
	
	$r = new Response((object) array( 'res' => 'json' ) );
	$r->ok($dom);
	print "\n";
	status("JSON output for complex data", 'OK');
	
	$r = new Response((object) array( 'res' => 'htm' ) ); // htm intentional, tests match
	$r->ok($dom);
	print "\n";
	status("Default HTML output for complex data", 'OK');

	unset($r);
	$r = new Response((object) array( 'res' => 'htm' ) ); // htm intentional, tests type matching
	
	Response::add_type_handler('.*\/htm.*', _encode_html, function($k, &$n, &$a, $d) {
		static $parents = array();
		static $was = 0;
				
		// track parents
	
		if ($was >= $d) // traversing up tree
			for($i = 0; $i < ($was - $d) + 1; $i++) array_pop($parents);
			
		$parents[] = $k;
		$p_at = count($parents) - 2;
		$p = $p_at >= 0 ? $parents[$p_at] : ''; 
		
		// remap node name
				
		switch ($k) {
			case 'chapters': 		$k = 'div'; break;
			case 'title': 			$k = 'h'.($d-1); break;
			case 'ideas':			$k = 'ul'; break;
			case 'li':
				if ($p === 'chapters') { // remap chapter LIs
					$k = 'section';				
					// also pull out the ID as an attribute
					if (array_key_exists('id', $n)) {
						$a .= " id='{$n['id']}'";
						unset($n['id']);
					}
				}
				break;
		}
	
		$was = $d; // track depth for parents path
	
		return $k;
	} );
	
	$r->ok($dom);
	print "\n";
	status("Mapped HTML output for complex data", 'OK');
	
	// TODO custom mapper / handler
}

function response_tests() {
	test("Response tests");
	$r = new Response((object) array( 'res' => 'json' ) );
	status("Created response object", 'OK');
	
	$r->ok( array('test' => 'test data') );
	print "\n";
	status("Responded with simple JSON transform", 'OK');
	
	$r = new Response((object) array( 'res' => 'html' ) );
	$r->ok( array('test' => 'test data') );
	print "\n";
	status("Responded with simple HTML transform", 'OK');
}

/* Test view helper(s) */
function simple_view_tests() {
	test("View tests");

	View::$root = realpath(__DIR__) . '/';
	$v = new View('test-view', array('name' => 'test'));
	status("Created simple view", 'OK');
	
}

/* Test PDO and wrapper(s) */
function database_tests() {
	test("DB/PDO tests");

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
	test("Service tests");
	
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


function test($text) {
	print "\n=[ $text ]========================================\n\n";
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

