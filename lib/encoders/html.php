<?php 

/* Simple HTML encoder */
function _encode_html($node,  $map = null) {
	static $d = 0;
	static $mapper;

	if ($mapper === null && $map !== null) $mapper = $map;

	if (!isset($d) || $map !== null) {
		$d = -1;
		return _encode_html(array('html' => array('body' => $node)));
	}

	$indent = str_repeat("\t", $d);	// indent for pretty printing

	if (is_string($node)) return print "\n$indent$node";
	elseif (is_array($node)) {

		// descend into child nodes

		$d++;
		foreach ($node as $k => &$v) {
			$a = '';

			if (empty($k) || is_numeric($k))
				$k = 'li'; // assume lists are LIs

			if (is_callable($mapper))
				$k = $mapper($k, $v, $a, $d);

			// print node

			print "\n$indent<$k$a>";
			_encode_html($v); // recurse
			print "\n$indent</$k>";
		}
		$d--;
	}
}

