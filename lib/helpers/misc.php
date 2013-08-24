<?php

namespace napkinware\presto;

/* Misc. Presto global helper functions */

// return the first valid value
function coalesce() { return array_shift(@array_filter(func_get_args())); }
function c() { return array_shift(@array_filter(func_get_args())); }
// get an array value at
function at($a, $k, $d = '') { return isset($a[$k]) ? $a[$k] : $d; }
// get wrapper (with default)
function get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
// post wrapper (with default)
function post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
// build a valid path (false if it's not valid)
function pathinate($p,$b = API_BASE) { return realpath($b . '/' . $p); }
// cleanup a uri string (this is not sanitize)
function cleanup($p) { return str_replace(array('-', '%20'), '_', $p); }
// simple trace (to apache error log)	
function trace() {
	if (PRESTO_TRACE == 0) return;
	$p = func_get_args();
	if (count($p))
		$p = array_map( function($v) { 
			if (is_array($v) || is_object($v)) return json_encode($v);
			else return $v;
		}, $p);
	error_log("PRESTO: ".implode(', ', $p));
}
