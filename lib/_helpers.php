<?php

/* Presto global helper functions */

class presto_lib {
	// return the first valid value
	static function coalesce() { return array_shift(@array_filter(func_get_args())); }
	static function _c() { return array_shift(@array_filter(func_get_args())); }
	// get an array value at
	static function _at($a, $k, $d = '') { return isset($a[$k]) ? $a[$k] : $d; }
	// get wrapper (with default)
	static function _get($k, $d = '') { return isset($_GET[$k]) ? $_GET[$k] : $d; }
	// post wrapper (with default)
	static function _post($k, $d = '') { return isset($_POST[$k]) ? $_POST[$k] : $d; }
	// build a valid path (false if it's not valid)
	static function _pathinate($p,$b = API_BASE) { return realpath($b . '/' . $p); }
	// cleanup a uri string (this is not sanitize)
	static function _cleanup($p) { return str_replace(array('-', '%20'), '_', $p); }
	// simple trace (to apache error log)	
	static function _trace() {
		if (PRESTO_TRACE == 0) return;
		error_log("PRESTO: ".implode(', ', func_get_args()));
	}
}
