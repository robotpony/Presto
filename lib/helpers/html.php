<?php /* Presto.md - Copyright (C) 2013 Bruce Alderson */

namespace napkinware\presto;

/* A simple HTML static class 

Generates HTML nodes using simple code. This is not a replacement for templates, rather it's a tool
for APIs to generate HTML snippets and microformats more predictably.

## USAGE: 

1. A simple node

	<?= html::p('Some text'); ?>

2. A simple node with classes

	<?= html::h1('Some text', array('a', 'b', 'c')); ?>	

*/
class html {
	
	/* Translate  call into a HTML node + params */
	public static function __callStatic($n, $p) {
		return self::_node($n, array_shift($p), array_shift($p));
	}
	
	/* ======================== Private helpers ======================== */
	
	// Return an HTML node
	private static function _node($n, $v, $a = null) {
		if (empty($n) || is_numeric($n)) throw new \Exception('Invalid node type', 500);
		$a = self::_attrs($a);
		return "<$n{$a}>$v</$n>";
	}
	
	// Return a set of attributes (k/v array)
	private static function _attrs($d) {
		if (!isset($d)) return '';
		
		$o = '';
		if (is_array($d) && $d !== array_values($d)) // associative, iterate
			foreach ($d as $k => $v) $o .= html::_attr($k, $v);		
		else // simple, treat as class
			$o = html::_attr('class', $d);
			
		return $o;
	}
	// Return an attribute (string or array of strings)
	private static function _attr($k, $v) {

		if (!isset($v) || !isset($k)) return '';
		elseif (is_array($v)) $v = implode(' ', $v);
		elseif (!is_string($v)) return '';

		return " $k='$v'";

	}
}

// Tests
// print html::a('Test link', array('href' => 'http://google.com'));