<?php include_once('./lib/presto.php'); 

if (PRESTO_DEBUG) { ?>
<h1>Presto debug mode</h1>
<?php } 

try {
	
	$p = new Presto();
	
	if (PRESTO_DEBUG) dump($p);
	
} catch (Exception $e) {
	print 'Presto error: ' . $e->getMessage();
}


?>