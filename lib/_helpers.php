<?php

/**
 * Automatically load classes that aren't included.

 	@param string $class (Required) The classname to load.
 	@return boolean Whether or not the file was successfully loaded.

 	See also: Presto::autoload_explicit
 */
function presto_autoloader($class) {
	// First look in the base directory for the web app
	$class_file = strtolower($class) . ".php";
	if (file_exists($class_file)) {
		require_once($class_file);
		return true;
	}
	// Next look in the Presto library directory
	$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
	$lib_file = $path . $class_file;
	if (file_exists($lib_file)) {
		require_once($lib_file);
		return true;
	}
	// We can't find it so we let other autoloaders try
	return false;
}

// Register the autoloader.
spl_autoload_register('presto_autoloader');


/* Presto global helper functions */

class presto_lib {
	// return the first valid value
	static function coalesce() { return array_shift(@array_filter(func_get_args())); }
	static function _d() { return array_shift(@array_filter(func_get_args())); }
	// build a valid path (false if it's not valid)
	static function _pathinate($p,$b = API_BASE) { return realpath($b . '/' . $p); }
}
