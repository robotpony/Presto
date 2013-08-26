<?php /* Presto - Copyright (C) 2013 Bruce Alderson */

namespace napkinware\presto;

/* Load/access settings from one or more json files

 */
class settings {
	private $files;
	private $base;

	/* Construct a settings object 
		
		$files - array of filename (without extension) and key value pair defaults
		$base 
	*/
	public function __construct($files = null, $base = '') {

		try {

			if (!isset($files))
				throw new \Exception('Missing configuration.');

			$this->base = $base;
			$this->files = $files;
			$this->loadSettings();

		} catch(\Exception $e) {

			trace($e->getMessage());
			throw $e;
		}
	}

	/* Handle missing settings files (last chance) */
	public function __get($n) {
		trace("Skipping missing '$n' settings (file not loaded)");
 		return "[missing file $n]";
	}

	/* ======================== Private helpers ======================== */

	/* Load the settings files */
	private function loadSettings() {

		foreach ($this->files as $name => $defaults) {

			$filename = "{$this->base}{$name}.json";

			if (!file_exists($filename))
				throw new \Exception("Missing settings ({$filename} not found)");

			$config = file_get_contents($filename);

			if (!$config || empty($config))
				throw new \Exception("Empty configuration file {$f->file}");

			$this->$name = new settingsFile($config, $filename, $defaults);
		}
		trace("Loaded $n settings.");
	}
}

/* One settings file */
class settingsFile {
	private $d;

	/* Set up the setting object */
	public function __construct($s, $f, $defaults = null) {

		// populate settings

		if (is_string($s))
			$this->d = json_decode($s); // decode from string
		elseif (is_array($s))
			$this->d = (object) $s; // from array, objectize
		elseif (is_object($s))
			$this->d = $s; // from object
		else
			throw new \Exception("Unknown configuration format found for $n: [$f] - $s");

		// merge in defaults, if any

		if ( $defaults && (is_object($defaults) || is_array($defaults)) )
			$this->d = (object) array_merge( (array) $defaults, (array) $this->d );
	}

	public function hasData() { return !empty($this->d); }

	// Get a setting
	public function __get($n) {

		if (property_exists($this->d, $n))
			return $this->d->$n;

		trace("Skipping missing '$n' setting (property does not exist)");
		
		return null;
	}
}
