<?php include_once('inc.php');

/* Presto: delegate API requests */

$p = null;

try {  
	$p = new Presto();	
	if (PRESTO_DEBUG) dump($p);
	
} catch (Exception $e) {
	$n = $e->getCode();
	$message = $e->getMessage();
	
	$detail = (is_object($p)) ? $p::call : $e->getTrace();
	
	$payload = array('message' => $message , 'code' => $n, 'detail' => $detail);	
	header("HTTP/1.0 $n API error");
	header("Content-Type: application/json");
	print json_encode( $payload );
}