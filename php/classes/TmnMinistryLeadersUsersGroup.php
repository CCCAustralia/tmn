<?php
if(file_exists('../classes/TmnUsersGroup.php')) {
	include_once('../classes/TmnUsersGroup.php');
}
if(file_exists('classes/TmnUsersGroup.php')) {
	include_once('classes/TmnUsersGroup.php');
}
if(file_exists('php/classes/TmnUsersGroup.php')) {
	include_once('php/classes/TmnUsersGroup.php');
}

class TmnMinistryLeadersUsersGroup extends TmnUsersGroup {
	
	public function __construct($ministry) {
		
		parent::__construct("Authorisers", "MINISTRY", $ministry);
		
	}

}

?>