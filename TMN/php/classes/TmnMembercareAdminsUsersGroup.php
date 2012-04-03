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

class TmnMembercareAdminsUsersGroup extends TmnUsersGroup {
	
	public function __construct() {
		
		parent::__construct("Admins", "POSITION", "MEMBERCARE_USER");
		
	}

}

?>