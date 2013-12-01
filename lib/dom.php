<?php /* Presto.md - Copyright (C) 2013 Bruce Alderson */

namespace napkinware\presto;

/* A simple DOM base class

	Provides:

	* a safe get mechanism
	*

	TODO

	* Consider two classes (DOM, DOM + HTML)
*/
class dom {

	protected $d;

	/* create a DOM instance

		$p - optional parameters (associative)
	*/
	public function __construct($p = null) {
		if (!$p) return;

		assert($p !== array_values($p) && !is_object($p),
			'Properties should be associative.');

		$this->d = array_merge(array(
			'title' => ''
		), $p);
	}
	/* Combine this DOM with an associative array */
	public function with($p) {
		assert($p !== array_values($p) && !is_object($p),
			'Properties should be associative.');

		$this->d = array_merge($this->d, $p);
		return $this;
	}
	public function JSON() { return json_encode($this->d); }

	public function __call($name, $arguments) {
		return "<span class='error'>dom::{$name}() not found</span>";
	}

	/* Dynamic get/set/isset/unset

		Allows DOM elements to be referenced safely
	*/
	public function __get($k) {
        if (!array_key_exists($k, $this->d)) {
	        return '';
        }

		return $this->d[$k];
    }
	public function __set($k, $v) {
		$this->d[$k] = $v;
    }

    public function __isset($k) {
        return array_key_exists($k, $this->d);
    }

    public function __unset($k) {
    	if (array_key_exists($k, $this->d))
    		unset($this->d[$k]);
    }

	/* Dump the class usefully as HTML */
	public function __toString() {
?>
<pre class="debug">
<?= __CLASS__; ?> :

<?= print_r($this); ?>

<?= print_r($this->d); ?>
</pre>
<?php
	}
}