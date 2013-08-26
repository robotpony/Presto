<?php

namespace napkinware\presto;


/* Presto routing tests */
class RoutingTest extends \PHPUnit_Framework_TestCase {

	protected $svc;

	/* Set up the basic API objects used in the tests */
	protected function setUp() {

	}

	public function testAuthorized() {
		$o = new Service( array( 
			'service' => 'http://presto.test'
		) );
		
		$info = $o->get('setup-tests/info.json');
		
		$this->assertTrue( isset($info) );
		
	}
}
