<?php include_once('./lib/Presto.php'); ?>

<h1>Presto debug mode</h1>

<?php 

try {
	
	$p = new Presto();
	dump($p);
	
} catch (Exception $e) {
	print 'Presto error: ' . $e->getMessage();
}


?>