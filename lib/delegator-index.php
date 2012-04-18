<?php include_once('config.php'); include_once(PRESTO);

/* Presto: delegate API requests */

try {  
	$p = new Presto();
	if (PRESTO_DEBUG) dump($p);

} catch (Exception $e) {
	$n = $e->getCode();
	$message = $e->getMessage();
	
	header("HTTP/1.0 $n API error");
	header("Content-Type: application/json");
	print json_encode( array('message' => $message , 'code' => $n ) );
}
?>