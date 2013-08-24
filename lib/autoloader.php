<?php

namespace napkinware\presto;

/* Automatically load classes that aren't included .

	Allows Presto to load most of its parts automatically.

 	@param string $class (Required) The classname to load.
 	@return boolean Whether or not the file was successfully loaded.

 	See also: Presto::autoload_delegate
 */
function presto_autoloader($c) {

	// Attempt to autoload based on include path

	$namespace = '';
	$parts = explode('\\', $c);	
	if (count($parts) > 0) {
		$c = array_pop($parts);
		$namespace = '\\' . implode('\\', $parts);
	}
	$class = $c;
	$file = strtolower($c) . ".php";

	if (!stream_resolve_include_path($file)) {		
		// Not found
		trace('Skipping auto-loading of ' . $file . ' (not found in ' . get_include_path() . ')');		
		return false; // let other autoloaders try
	}

	include_once($file);

	trace('Auto loaded class:', $namespace, $c);

	return true;
}

// Register the autoloader.
\spl_autoload_register('napkinware\\presto\\presto_autoloader');


// Delegation autoloading
function autoload_delegate(&$call) {

	$in = $call->container;
	$error = "API `$call->class` not found";
	
	if ( !stream_resolve_include_path($call->file) ) {
		$extra = " ({$call->file} not found).";
		
		if ( !empty($in) && !is_dir($in) )
			$extra = " ({$call->file} not found, $in missing)."; // aid debugging of routes-in-folders
			
		throw new \Exception($error .$extra, 404);
	}

	if ( !(include_once $call->file) )
		throw new \Exception($error . " ({$call->file} not loadable).", 500);

	// determine the namespace by looking up the loaded class
	$classes = get_declared_classes();
	$classes = preg_grep("/(?:^{$call->class}|\\\\{$call->class})$/", $classes);
	if (count($classes) !== 1)
		trace('Found an unexpected number of matches for', $call->class, json_encode($classes));
	$call->class = array_pop($classes);

	trace('Auto loaded API', $call->class, json_encode($call));

	return new $call->class;
}
