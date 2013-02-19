<?php

/* Presto global helper functions */

class presto_lib {
	// return the first valid value
	static function coalesce() { return array_shift(@array_filter(func_get_args())); }
	static function _d() { return array_shift(@array_filter(func_get_args())); }
	// build a valid path (false if it's not valid)
	static function _pathinate($p,$b = API_BASE) { return realpath($b . '/' . $p); }
	static function cleanup($p) { return str_replace('-', '_', $p); }
	// simple trace (to apache error log)	
	static function _trace() {
		if (PRESTO_DEBUG == 0) return;
		error_log("PRESTO: ".implode("\n\t", func_get_args()));
	}
}
