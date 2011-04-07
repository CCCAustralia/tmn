<?php
include_once 'php/dbconnect.php';
include_once 'php/classes/Tmn.php';
include_once 'lib/FirePHPCore/fb.php';
ob_start();

$authobj = new Tmn("php/logs/admin.php.log");
//$authobj->authenticate();
if (!$authobj->isAuthenticated() || !$authobj->getUser()->isAdmin()) {
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
fb($_GET);
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
			if ($_GET['edit'] == $fieldname) {
		////array name
				echo "<tr><td>".$fieldname."</td><td>";
				foreach (json_decode($value) as $key => $subvalue) {
					if ($subvalue == PHP_INT_MAX) {
						echo $subvalue."<br />";	//don't allow them to edit intmax
					} else {
		////edit array elements
						echo "<input name= ".$fieldname.$key." type=textarea value=".$subvalue."><input type=submit value='Save' /><br />";
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
			if ($_GET['edit'] == $fieldname) {
		////value name, edit box and save button
				echo "<tr><td>".$fieldname."</td><td><input name= ".$fieldname." type=textarea value=".$value."><input type=submit value='Save' /></td></tr>";
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
}
echo "</table></form><br /><br /><br />";

////Authorisers
//ministry leader input
//get authorisers
$sql = "SELECT MINISTRY, GUID FROM `Authorisers` WHERE 1";
$sql = mysql_query($sql);
$authorisers = array();
for ($index = 0; $index < mysql_num_rows($sql); $index++) {
	//store them in an array
	$temparray = mysql_fetch_assoc($sql);
	$authorisers[$temparray['MINISTRY']] = $temparray['GUID'];
}

$sql = "SELECT GUID, FIRSTNAME, SURNAME, FIN_ACC_NUM, IS_TEST_USER FROM `User_Profiles` WHERE 1";
$sql = mysql_query($sql);
$userlist = array();
for ($index = 0; $index < mysql_num_rows($sql); $index++) {
	//store them in an array
	$temparray = mysql_fetch_assoc($sql);

	if ($temparray['IS_TEST_USER'] == 0) {	//don't add test accounts
		$userlist[$temparray['GUID']] = $temparray;
	}
}

fb($userlist);

foreach ($authorisers as $ministry => $guid) {
	$authorisers[$ministry] = array('GUID' => $guid, 'NAME' => $userlist[$guid]['FIRSTNAME']." ".$userlist[$guid]['SURNAME']);
	if (isset($_GET[$ministry])) {
		$sql = "UPDATE `Authorisers` SET `GUID` = '".$_GET[$ministry]."' WHERE MINISTRY = '".$ministry."'";
		$sql = mysql_query($sql);
		echo "<br />".$userlist[$guid]['FIRSTNAME']." ".$userlist[$guid]['SURNAME']. " saved as ".$ministry." TMN authoriser!<br />";
	}
}
fb($authorisers);

$optionlist = "";
foreach ($userlist as $guid => $user) {
	$optionlist .= "<option value='".$guid."'>".$user['FIRSTNAME']." ".$user['SURNAME']." - ".$user['FIN_ACC_NUM']."</option>";
}

	echo "<form name='".$ministry."' method=GET onsubmit='admin.php'>";
echo "<table border=1><th>Ministry:</th><th>Authoriser:</th>";
foreach ($authorisers as $ministry => $authuser) {
	echo "<tr><td>$ministry:</td><td>";

	//find the location of the authorisers guid
	$personalcombo = split($authuser['GUID'], $optionlist);//($combobox, 0, strpos($combobox, $authuser['GUID'])+strlen($authuser['GUID']) + 1);
	fb($personalcombo);
	fb($authuser['GUID']);
	
	//select the authoriser in the combo box
	if ($personalcombo[1] != NULL) {
		echo "<select name='".$ministry."'>";
			//optionlist with inserted "selected" parameter
			echo $personalcombo[0];
			echo $authuser['GUID'];
			echo "' selected";
			echo substr($personalcombo[1],1);
		echo "</select>";
	} else {
		fb("Authoriser ".$authuser['GUID']." not found");
		echo "<select name='".$ministry."'>";
		echo $optionlist;
		echo "<option value='".$authuser['GUID']."' selected>".$authuser['GUID']." - Name not in database! Never done a TMN?</option>";
		echo "</select>";
	}
	echo "<input type='submit' value='Save' /></td>";
	echo "</tr>";
}
echo "</table></form>";


echo "</body></html>";
?>














