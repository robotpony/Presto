<?php include_once('inc.php');

/* Presto request delegator

	Symlink or copy this into your API folder. Requires .htaccess (see htaccess-example for details)
*/
$p = null;
try {

	$p = new Presto();

} catch (\Exception $e) {

	/* Last chance exception handler
		Attempts to produce sane RESTful output if other error mechanisms have failed. Limited to JSON responses.
	*/

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

	if (PRESTO_TRACE) $payload[PRESTO_TRACE_KEY] = Presto::trace_info();

	error_log(json_encode($payload)); // also send to syslog

	header("HTTP/1.0 $n API error");
	header("Content-Type: application/json");
	print json_encode( $payload );
}