<?php // Presto global helper functions

// Dump output for debugging
function dump() { 
?><pre class="dump"><?php
	print(
		implode("\n", 
			array_map(
				function ($o) { if (is_array($o)) print_r($o); else print "$o\n";  }, 
				func_get_args()
			)
		)
	);
?></pre><?php 
}

// Load classes automatically
function __autoload($c) {
		require_once($c . '.php'); 
}

function coalesce() {
  return array_shift(@array_filter(func_get_args()));
}
?>