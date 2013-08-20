<?php
/* Automatically load classes that aren't included .

	Allows Presto to load most of its parts automatically.

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


// Delegation autoloading=
function autoload_delegate($call) {
	$in = $call->container;
	$error = "API `$call->class` not found";
	
	if ( !stream_resolve_include_path($call->file) ) {
		$extra = " ({$call->file} not found).";

		if ( !empty($in) && !is_dir($in) )
			$extra = " ({$call->file} not found, $in missing)."; // aid debugging of routes-in-folders
			
		throw new Exception($error . $extra, 404);
	}
	
	if ( !(require_once $call->file) )
		throw new Exception($error . " ({$call->file} not loadable).", 500);

	
}
