<?php

/* A simple PrestoPHP class for adding execution duration profiling to debugging output.

	Usage:
	
		Profiler::track($key, $extra);
	
	* Turns a timer on to track duration of process identified by $key (turns it off if it has been running.
	* $extra is an optional array that is JSON encoded and added to debug output

*/
class Profiler {

	private static $processes = array();

	public static function track($key, $extra = null) {
		if (!array_key_exists($key, self::$processes)) {
			// initialize
			self::$processes[$key] = (object) array(
				'duration' => 0,
				'duration_units' => 'microseconds',
				'last_started' => microtime(true) * 1000000,
				'state' => 'running',
				'extra' => array()
			);
		}
		
		
		$p = &self::$processes[$key];
		
		if ($p->state === 'running') {
			// toggle off
			$p->duration += microtime(true) * 1000000 - $p->last_started;
			$p->state = 'stopped';
		}
		else {
			// toggle on
			$p->last_started = microtime(true) * 1000000;
			$p->state = 'running';
		}
	
		if (is_array($extra))
			$p['extra'] = array_merge($p['extra'], $extra);
	}
	
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