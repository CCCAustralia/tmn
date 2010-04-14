<?php
include_once "dbconnect.php";
include_once "logger.php";

$COOKIEPATH = "/TMN/";

$connection = db_connect();
$LOGFILE = "logs/internal_transfers.log";

//session needs to be FAN, remove when multiple sessions is implemented
$sql = mysql_query('SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID="'.$_POST['session'].'";');
$res = mysql_fetch_assoc($sql);
$session = $res['FIN_ACC_NUM'];


//////////////////////////////////FIX SQL INJECTION RISK//////////////////////////////////


$returnvalue = '{transfers: []}';

switch ($_POST['mode']){
	case 'get':
	
		$total = 0;
		$sql = "SELECT * FROM Internal_Transfers WHERE SESSION_ID='".$session."'";
		$rows = mysql_query($sql);
		
		for($row_count=0; $row_count < mysql_num_rows($rows); $row_count++){
			
			$row = mysql_fetch_assoc($rows);
			$returndata .= "{";
			
			foreach ($row as $key=>$value) {
				$returndata .= $key.": '" . $row[$key] . "',";
				if ($key == "TRANSFER_AMOUNT"){
					$total += $row[$key];
				}
			}
			
			$returndata = trim($returndata,",");
			$returndata .= "},";
		}
		$returndata = trim($returndata,",");
		$returnvalue = "{success: true, transfers: [" . $returndata . "] }";

		break;
	case 'add':
		$sql = "INSERT INTO Internal_Transfers VALUES (NULL, '" . $session . "', '" . $_POST['name'] . "', '" . $_POST['amount'] . "')";
		mysql_query($sql);
		$returnvalue = "{success: true}";
		break;
	case 'update':
		$sql = "UPDATE Internal_Transfers SET TRANSFER_NAME='" . $_POST['name'] . "', TRANSFER_AMOUNT='" . $_POST['amount'] . "' WHERE TRANSFER_ID=".$_POST['id'];
		mysql_query($sql);
		$returnvalue = "{success: true}";
		break;
	case 'remove':
		$sql = "DELETE FROM Internal_Transfers WHERE TRANSFER_ID=".$_POST['id']." AND TRANSFER_NAME='".$_POST['name']."'";
		mysql_query($sql);
		$returnvalue = "{success: true}";
		break;
	default:
		$returnvalue = "{success: false}";
}

echo $returnvalue;

//$connection.close();

?>