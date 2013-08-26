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
		
	/** Missing configuration
	 * @expectedException Exception
	 * @expectedExceptionCode 400 
	 */
	public function testAuthorized() {
		$o = new Service( array( 
			'service' => 'https://api.twitter.com/1.1'
		) );
		
		$o->get_help_json('tos');
		
//		$this->assertTrue(  );
		
	}
}
