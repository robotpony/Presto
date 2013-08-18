<?php
/* Automatically load classes that aren't included .

	Allows Presto to load most of its parts automatically.

 	@param string $class (Required) The classname to load.
 	@return boolean Whether or not the file was successfully loaded.

 	See also: Presto::autoload_delegate
 */
function presto_autoloader($c) {

	// Attempt to autoload based on include path
	
	$class = strtolower($c) . ".php";
	
	if (!stream_resolve_include_path($class)) {
		
		// Not found
		
		presto_lib::_trace('Skipping auto-loading of ' . $class . '(not found in ' . get_include_path() . ')');		
		return false; // let other autoloaders try
	}

	require_once($class);
	return true;
	
}

// Register the autoloader.
spl_autoload_register('presto_autoloader');


// Delegation autoloading
function autoload_delegate($call) {

	$in = $call->container;
	$error = "API `$call->class` not found";
	
	if ( !stream_resolve_include_path($call->file) ) {
		$extra = " ({$call->file} not found).";
		
		if ( !empty($in) && !is_dir($in) )
			$extra = " ({$call->file} not found, $in missing)."; // aid debugging of routes-in-folders
			
		throw new Exception($error .$extra, 404);
	}
	
	if ( !(require_once $call->file) )
		throw new Exception($error . " ({$call->file} not loadable).", 500);

	
}
