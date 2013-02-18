<?php

/* Presto global helper functions */

class presto_lib {
	// return the first valid value
	static function coalesce() { return array_shift(@array_filter(func_get_args())); }
	static function _d() { return array_shift(@array_filter(func_get_args())); }
	// build a valid path (false if it's not valid)
	static function _pathinate($p,$b = API_BASE) { return realpath($b . '/' . $p); }
}
