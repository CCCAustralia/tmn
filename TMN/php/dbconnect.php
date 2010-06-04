<?php
function db_connect(){

$db_name ="mportal_tmn";
$connection = @mysql_connect("localhost", "mportal","***REMOVED***") or die(mysql_error());
$db = @mysql_select_db($db_name,$connection) or die(mysql_error());

return $connection;
} //end function db_connect()