<?php

namespace napkinware\presto;

include '../lib/helpers/settings.php';

/* Basic reporting API tests */
class SettingsClassTest extends \PHPUnit_Framework_TestCase {

	protected $t;

	/* Set up the basic API objects used in the tests */
	protected function setUp() {

	}

	/** Test missing settings

	 * @expectedException Exception
	 * @expectedExceptionCode 0
	 */
	public function testMissingFiles() {
		$o = new settings();
	}

	/** Test non-existent file

	 * @expectedException Exception
	 * @expectedExceptionCode 0
	 */
	public function testInvalidFiles() {
		$o = new settings(array('file-that-doesnt-exist' => array( 'nothing' => 0 )));
	}

	/** Test general key access
		
	*/
	public function testSettingsKeys() {

		// load some simple json data
		$o = new settings(array(
			'simple' => array(
				/* set up some simple defaults*/
				'stringThing' => '',
				'otherKey' => false )
			),
			'data/'
		);
		
		$simple = $o->simple;

		$this->assertNotNull($simple); // settings file object ok
		$this->assertNotEmpty($simple->stringThing); // settings value ok
		$this->assertNotEmpty($simple->intThing); // numeric settings value ok
		$this->assertTrue($simple->otherKey === false); // default settings value ok

		$this->assertTrue( $simple->thisKeyDoesNotExist === null ); // non-existant
	}
}
