<?php
include_once "dbconnect.php";
include_once "logger.php";

$connection = db_connect();
$LOGFILE = "logs/combofill.log";

$tablename = $_POST['mode'];

$issql = true;
if (!strstr($tablename, ' '))
	$issql = false;

if ($tablename == 'User_Profiles' || $tablename == 'Sessions' || $tablename == 'Authorising' || $issql)
	die();

$rows = "SELECT * FROM ".$tablename;
$rows = mysql_query($rows);
//$returndata = array();

for ($i = 0; $i < mysql_num_rows($rows); $i++) {
	$r = mysql_fetch_assoc($rows);
	//$returndata[$m['MINISTRY_ID']] = $m['MINISTRY_ID'];
	$returndata .= "{";
	foreach ($r as $k=>$v) {
		$returndata .= "\"".$k."\": \"".$r[$k]."\",";
		//$returndata .= "{ministry_id: '".$r['MINISTRY_ID']."',";
		//$returndata .= "ministry_levy: '".$r['MINISTRY_LEVY']."'},";
	}
	$returndata = trim($returndata, ",");
	$returndata .= "},";
}

$returndata = trim($returndata,",");

echo '{	'.$tablename.':['.$returndata.'] }';

//$connection.close();


?>