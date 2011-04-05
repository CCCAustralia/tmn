<?php
include_once 'php/dbconnect.php';
include_once 'php/classes/Tmn.php';

$authobj = new Tmn("php/logs/admin.php.log");
$authobj->authenticate();
if (!$authobj->getUser()->isAdmin()) {
	die("You don't have permission to access this page. If you think you should be able to access this page, contact <a href=\"mailto:tech.team@ccca.org.au\">tech.team@ccca.org.au</a>");
}

$connection = db_connect();

$sql = "SELECT * FROM `Constants` WHERE 1";
$sql = mysql_query($sql);
$constants = mysql_fetch_assoc($sql);
$savestring = "";
$savefield = "";
$versionnumber = "2-2-0";

echo "<html><body><form method=GET onsubmit='admin.php'><table border=1><th>Field Name:</th><th>Stored Value:</th>";
foreach ($constants as $fieldname => $value) {
	if (!is_null($fieldname)){
		//loop through each parameter
		foreach ($_GET as $savedkey => $savedvalue) {
			//check if it matches a field name
			if (substr($savedkey, 0, strlen($fieldname)) == $fieldname) {
				//form json if array
				if ($savestring != "" && substr($savestring, 0, 1) != "[") {
					$savestring = "[".$savestring;
				}
				//concat with value
				$savestring .= $savedvalue.",";
				$savefield = substr($savedkey, 0, strlen($fieldname));
				
			}
		}
		//remove json extras for non-arrays
		if (substr($savestring, strlen($savestring) - 1) == ",") {
			$savestring = substr($savestring, 0, strlen($savestring) - 1);
		}
		//form json if array
		if (substr($savestring, 0, 1) == "[" && substr($savestring, strlen($savestring) - 1) != "]") {
			$savestring .= "]";
		}
		
		//echo "<tr><td>".$savestring."</td><td></td></tr>";
		
//////  array output  //////
		if (is_array(json_decode($value))) {
	////edit mode - text box and save button
			if ($_REQUEST['edit'] == $fieldname) {
		////array name
				echo "<tr><td>".$fieldname."</td><td>";
				foreach (json_decode($value) as $key => $subvalue) {
					if ($subvalue == PHP_INT_MAX) {
						echo $subvalue."<br />";	//don't allow them to edit intmax
					} else {
		////edit array elements
						echo "<input name= ".$fieldname.$key." type=textarea value=".$subvalue."><input type=submit value='save' /><br />";
					}
				}
				echo "</tr>";
			} else {	
	////normal mode - link to edit mode
		////link to edit mode for array
				echo "<tr><td><a href=admin.php?edit=".$fieldname." title='Edit value for ".$fieldname."'>".$fieldname."</a></td><td>";
				foreach (json_decode($value) as $subvalue) {
					echo $subvalue."<br />";
				}
				echo "</tr>";
			}
//////  value output  //////
		} else {
	////edit mode - text box and save button
			if ($_REQUEST['edit'] == $fieldname) {
		////value name, edit box and save button
				echo "<tr><td>".$fieldname."</td><td><input name= ".$fieldname." type=textarea value=".$value."><input type=submit value='save' /></td></tr>";
			} else {	
	////normal mode - link to edit mode
		////value name, value
				echo "<tr><td><a href=admin.php?edit=".$fieldname." title='Edit value for ".$fieldname."'>".$fieldname."</a></td><td>".$value."</td></tr>";
			}
		}
	}
	
}

//form the save sql
if ($savefield != "") {
	$sql = "UPDATE `Constants` SET `".$savefield."` = '".$savestring."' WHERE VERSIONNUMBER = '".$versionnumber."'";
	$sql = mysql_query($sql);
	mysql_close($connection);
	
}
echo "</table></form>";

//ministry leader input
echo "</body></html>";
?>