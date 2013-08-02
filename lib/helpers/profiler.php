<?php
include_once(PRESTO_BASE.'/_helpers.php');
define('PROFILER_TRACE_KEY', '_profiler_trace');

/* A simple PrestoPHP class for adding execution duration profiling to debugging output.

	Usage:
	
		Profiler::track($key, $extra); // initialize and start tracking execution time (with optional extra debugging data)
		Profiler::stop($key); // stop tracking execution time
		Profiler::restart($key, $extra); // restart tracking execution time (optional $extra data is merged with existing optional $extra debugging data)
		Profiler::profile($key); // return current profile data
	
	* $extra is an optional array that is JSON encoded and added to debug output
	* All profile data is automatically added to Presto trace output if Presto trace is enabled (see _config)

*/
class Profiler {

	private static $processes = array();

	/*
		Set up inital tracking of a process identified by $key and start timing duration.
	*/
	public static function track($key, $extra = null) {
	
		if (isset($extra) && !is_array($extra)) {
			presto_lib::_trace( __METHOD__, "Failed to track [$key]: optional extra data must be an array.");
			return;
		}
			
		if (!array_key_exists($key, self::$processes)) {
			// initialize
			self::$processes[$key] = (object) array(
				'duration' => 0,
				'duration_units' => 'microseconds',
				'last_started' => microtime(true) * 1000000,
				'state' => 'running',
				'extra' => $extra
			);
		}
		else
			presto_lib::_trace( __METHOD__, "[$key]: is already being tracked.");
	}
	
	/*
		Turn timer off for process identified by $key and calculate duration since the last time it was turned on.
	*/
	public static function stop($key) {

		if (empty(self::$processes[$key])) {
			presto_lib::_trace( __METHOD__, "Failed to stop [$key]: it isn't being tracked.");
			return;
		}
		
		$p = &self::$processes[$key];
		
		if ($p->state === 'running') {
			// toggle off
			$p->duration += microtime(true) * 1000000 - $p->last_started;
			$p->state = 'stopped';
		}
	}
	
	/*
		Restart timer for process identified by $key.
		
		* Optional $extra debugging data is merged with any set in Profiler::track
	*/
	public static function restart($key, $extra = null) {
		
		if (empty(self::$processes[$key])) {
			presto_lib::_trace( __METHOD__, "Failed to restart [$key]: it isn't being tracked.");
			return;
		}
	
		if (isset($extra) && !is_array($extra)) {
			presto_lib::_trace( __METHOD__, "Failed to restart [$key]: optional extra data must be an array.");
			return;
		}
		
		$p = &self::$processes[$key];
		
		if ($p->state === 'stopped') {
			// toggle on
			$p->last_started = microtime(true) * 1000000;
			$p->state = 'running';
		}
	
		if (is_array($extra))
			$p['extra'] = array_merge($p['extra'], $extra);
	}
	
	/*
		Return the profile of the process identified by $key.
	*/
	public static function profile($key) {
		
		if (empty(self::$processes[$key])) return;
		
		$p = &self::$processes[$key];
	
		if ($p->state === 'running') {
			// calc current durations for output
			$p->duration += microtime(true) * 1000000 - $p->last_started;
		}

		return $p;
	}
	
	/*
		Return all profile data.
	*/
	public static function profiles() {
		
		if (empty(self::$processes)) return;
	
		foreach (self::$processes as &$p) {
			if ($p->state === 'running') {
				// calc current durations for output
				$p->duration += microtime(true) * 1000000 - $p->last_started;
			}
		}
		
		return self::$processes;
	}
}