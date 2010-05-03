<?php

include_once("logger.php");
include_once("dbconnect.php");

$LOGFILE = "./logs/submit_tmn.log";


$connection = db_connect();

//session needs to be FAN, remove when multiple sessions is implemented
$sql = mysql_query('SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID="'.$_POST['session'].'";');
$res = mysql_fetch_assoc($sql);
$session = $res['FIN_ACC_NUM'];
$jsonObj = json_decode(stripslashes($_POST['json']), true);

$sql = mysql_query('SELECT * FROM Sessions WHERE SESSION_ID="'.$session.'";');

if (mysql_num_rows($sql) == 1){
	$sql = 'UPDATE Sessions SET JSON="'.$_POST['json'].'" WHERE SESSION_ID="'.$session.'";';
	$res = mysql_query($sql);
} else {
	$sql = 'INSERT INTO Sessions (SESSION_ID,GUID,SESSION_NAME,FIN_ACC_NO,JSON) VALUES ("'.$session.'", "'.$_POST['session'].'", "'.$jsonObj['tmn_data']['firstname'].' '.$jsonObj['tmn_data']['surname'].'", "'.$session.'", "'.$_POST['json'].'");';
	//$sql = 'INSERT INTO Sessions (SESSION_ID,JSON) VALUES ("'.$session.'", "'.$_POST['json'].'");';
	$res = mysql_query($sql);
}

?>