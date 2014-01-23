<?php

/** A data model for the current user

	* data supplied from `GET /v4/people/$personID.json`

*/
class user {

	public function __construct($userData) {
		print_r($userData);
	}
}

