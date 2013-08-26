<?php

namespace napkinware\presto;


/* Presto service wrapper tests */
class ServiceTests extends \PHPUnit_Framework_TestCase {

	protected $svc;

	/* Set up the basic API objects used in the tests */
	protected function setUp() {

	}

	/** Missing configuration
	 * @expectedException Exception
	 * @expectedExceptionCode 0     
	 */
	public function testMissingConfig() {
		$o = new Service();
	}
	/** Invalid configuration
	 * @expectedException Exception
	 * @expectedExceptionCode 0     
	 */
	public function testInvalidConfig() {
		$o = new Service( array('bob' => 'is your uncle') );
	}
		
	public function testSimpleGet() {
		$o = new Service( array( 
			'service' => 'http://presto.napkinware.com/'
		) );
		
		$v = $o->get('setup-tests/info.json');
		
		$this->assertTrue( isset($v) );
		
	}
}
