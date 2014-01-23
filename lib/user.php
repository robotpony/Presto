<?php

/** A data model for the current user

	* data supplied from `GET /v4/people/$personID.json`

*/
class user {

	public $u;

	public function __construct($userData) { $this->u = $userData; }
	
	public function __get($key) {
		switch ($key) {
			case 'Account' :
				return !empty($this->u->enrolments->account) ? $this->u->enrolments->account : 0;
			case 'AccountID' :
				return !empty($this->u->enrolments->account->ID) ? $this->u->enrolments->account->ID : 0;
			case 'AccountName' :
				return !empty($this->u->enrolments->account->name) ? $this->u->enrolments->account->name : 0;

			default:
				return !empty($this->u->{$key}) ? $this->u->{$key} : '';
		}
	}
}

