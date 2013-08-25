<?php

namespace napkinware\presto;

include '../lib/helpers/settings.php';

/* Basic reporting API tests */
class SettingsClassTest extends \PHPUnit_Framework_TestCase {

	protected $t;

	/* Set up the basic API objects used in the tests */
	protected function setUp() {

	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 0     
	 */
	public function testMissingFiles() {
		$o = new settings(); // missing settings, will fail
	}
	/**
	 * @expectedException Exception
	 * @expectedExceptionCode 0     
	 */
	public function testInvalidFiles() {
		// invalid settings should fail
		$o = new settings(array('file-that-doesnt-exist' => array( 'nothing' => 0 ))); 
	}
	
	
	/* Basic can-connect and get some data */
	public function testAuthorized() {

	}
	
	public function testListings() {}

}
