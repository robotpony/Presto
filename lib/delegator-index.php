<?php include_once('inc.php');

/* Presto: delegate API requests */

$p = null;

try {
	$p = new Presto();
} catch (Exception $e) {
	$n = $e->getCode();
	$message = $e->getMessage();
	$via = $e->getPrevious();
	
	if (PRESTO_DEBUG) {
		$detail = (is_object($p)) ? $p::call : $e->getTrace();
		$payload = array('message' => $message , 'code' => $n, 'detail' => $detail);
	} else {
		$payload = array('message' => $message , 'code' => $n);
	}

	if ($via) $payload['error'] = array('message' => $via->getMessage(), 'code' => $via->getCode());

	error_log(json_encode($payload)); // also send to syslop

	header("HTTP/1.0 $n API error");
	header("Content-Type: application/json");
	print json_encode( $payload );
}