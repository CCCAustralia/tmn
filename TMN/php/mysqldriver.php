<?php
class MySqlDriver {
	private $connection;
	
	public function __construct(){
		
		$db_name ="mportal_tmn";
		$this->connection = @mysql_connect("localhost", "mportal","***REMOVED***") or die(mysql_error());
		$db = @mysql_select_db($db_name,$this->connection) or die(mysql_error());
	} //end function db_connect()
	
	public function getConnection(){
		return $this->connection;
	}
	
	public function __destruct() {
		//mysql_close( $this->$connection );
	}
}