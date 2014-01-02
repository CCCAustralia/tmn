<?php
include_once 'php/dbconnect.php';
include_once 'php/classes/Tmn.php';
include_once 'lib/FirePHPCore/fb.php';
$DEBUG = 1;
ob_start();
try {
Tmn::authenticate();
//$authobj->authenticate();
	$authobj = new Tmn("php/logs/admin.php.log");
	if (!$authobj->isAuthenticated() || !$authobj->getUser()->isAdmin()) {
		die("You don't have permission to access this page. If you think you should be able to access this page, contact <a href=\"mailto:tech.team@ccca.org.au\">tech.team@ccca.org.au</a>");
	}
} catch (Exception $e) {
	echo 'Authentication failed due to Database Error. Please contact <a href="tech.team@ccca.org.au">tech.team@ccca.org.au</a>.';
}

$connection = db_connect();
//////////    SET UP    //////////
function fetchUserList() {
	////start userlist
	$returnlist = array();
	$sql = "SELECT GUID, FIRSTNAME, SURNAME, FIN_ACC_NUM FROM `User_Profiles`";
	$sql = mysql_query($sql);
	for ($index = 0; $index < mysql_num_rows($sql); $index++) {
		//store them in an array
		$temparray = mysql_fetch_assoc($sql);
	
		/* Admin can filter out test accounts
		if ($temparray['IS_TEST_USER'] == 0) {	//don't add test accounts
		}
		*/
		$returnlist[$temparray['GUID']] = $temparray;
	}
	if($DEBUG)fb($returnlist);
	return $returnlist;
////end userlist
}
function fetchAuthList() {
	////start authorisers
	$sql = "SELECT MINISTRY, GUID FROM `Authorisers` WHERE 1";
	$sql = mysql_query($sql);
	$returnlist = array();
	for ($index = 0; $index < mysql_num_rows($sql); $index++) {
		//store them in an array
		$temparray = mysql_fetch_assoc($sql);
		$returnlist[$temparray['MINISTRY']] = $temparray['GUID'];
	}
	
	if($DEBUG)fb($returnlist);
	////end authorisers
	return $returnlist;
}

function createOptionList($tempuserlist) {
	$returndata = "";
	foreach ($tempuserlist as $guid => $user) {
		$returndata .= "<option value='".$guid."'>".$user['FIRSTNAME']." ".$user['SURNAME']." - ".$user['FIN_ACC_NUM']."</option>";
	}
	//if($DEBUG)fb($returndata);
	return $returndata;
}

$userlist = fetchUserList();
$authorisers = fetchAuthList();
$optionlist = createOptionList($userlist);

//////////    END SET UP    //////////

$sql = "SELECT * FROM `Constants` WHERE 1";
$sql = mysql_query($sql);
$constants = mysql_fetch_assoc($sql);
$savestring = "";
$savefield = "";
$versionnumber = "2-2-0";

echo "<html><body><table border=1>";
echo "<th colspan=2>Instructions:</th>";
echo "<tr><td colspan=2>Pull rates and limits from \"" . date("Y", time()) ." annual rates chart\" (member care)<br /><br />";
echo "Tax values are from page 2 of ATO document:<br /><a target='_blank' href='http://www.ato.gov.au/Search/GoogleSearchResults.aspx?q=1004&site=atogovR3&requiredfields=(ato_reference_natnumber:1004)'>\"NAT1004\" - Shedule 1 - statement of formulas for calculating amounts to be withheld from payments.</a><br />";
echo "<ul><li>x_resident: scale 7, '$' column</li>";
echo 	"<li>a_resident: scale 7, 'a' column</li>";
echo 	"<li>b_resident: scale 7, 'b' column</li>";
echo 	"<li>x_non_resident: scale 3, '$' column</li>";
echo 	"<li>a_non_resident: scale 3, 'a' column</li>";
echo 	"<li>b_non_resident: scale 3, 'b' column</li></ul>";
echo "</td></tr>";
echo "<th>Field Name:</th><th>Stored Value:</th>";
if($DEBUG)fb($_POST);
if($DEBUG)fb($constants);
foreach ($constants as $fieldname => $value) {
	if (!is_null($fieldname)){
		//loop through each parameter
		foreach ($_POST as $savedkey => $savedvalue) {
			//check if it matches a field name
			if (substr($savedkey, 0, strlen($fieldname)) == $fieldname) {
				//update the constant with the saved value for output
				$value = $savedvalue;
				//form json if array
				if ($savestring != "" && substr($savestring, 0, 1) != "[") {
					$savestring = "[".$savestring;
				}
				//concat with value
				$savestring .= $savedvalue.",";
				$savefield = substr($savedkey, 0, strlen($fieldname));
				
				if($DEBUG){fb($savestring);}
			}
		}
		//remove json extras for non-arrays
		if (substr($savestring, strlen($savestring) - 1) == ",") {
			$savestring = substr($savestring, 0, strlen($savestring) - 1);
		}
		//form json if array
		if (substr($savestring, 0, 1) == "[" && substr($savestring, strlen($savestring) - 1) != "]") {
			$savestring .= "]";
			//update the constant with the saved value for output
			$value = $savestring;
		}
		
		//echo "<tr><td>".$savestring."</td><td></td></tr>";
		
//////  array output  //////
		if (is_array(json_decode($value))) {
	////edit mode - text box and save button
			//if (true) {//$_GET['edit'] == $fieldname) {
		////array name
				echo "<tr><td>".$fieldname."</td><td><form method=POST onsubmit='admin.php'>";
				$arrayindex = 0;
				foreach (json_decode($value) as $key => $subvalue) {
						echo "<input name=".$fieldname.key . $arrayindex." type=textarea value=".$subvalue;
					if ($subvalue == PHP_INT_MAX) {
						echo " readonly=readonly style='background-color:grey;'";	//don't allow them to edit intmax
					}
		////edit array elements
					echo "><input type=submit value='Save' /><br />";
					$arrayindex++;
				}
				echo "</form></td></tr>";
			/*} else {	
	////normal mode - link to edit mode
		////link to edit mode for array
				echo "<tr><td><a href=admin.php?edit=".$fieldname." title='Edit value for ".$fieldname."'>".$fieldname."</a></td><td>";
				foreach (json_decode($value) as $subvalue) {
					echo $subvalue."<br />";
				}
				echo "</tr>";
			}*/
//////  value output  //////
		} else {
	////edit mode - text box and save button
			//if (true) {//$_GET['edit'] == $fieldname) {
		////value name, edit box and save button
				if ($fieldname == "FINANCE_USER"){
			////FINANCE_USER
					echo "<tr><td>".$fieldname."</td><td><form name='".$fieldname."' method=POST onsubmit=admin.php>";

					//Output a combobox of users with the current database value selected
					if($DEBUG){fb($value);}
					$personalcombo = split($value, $optionlist);
					if($DEBUG){fb($personalcombo);}
					if ($personalcombo[1] != NULL) {
						echo "<select name='".$fieldname."'>";
							//optionlist with inserted "selected" parameter
							echo $personalcombo[0];
							echo $value;
							echo "' selected";
							echo substr($personalcombo[1],1);
						echo "</select><input type=submit value='Save' />";
					}
					echo "</form></td>";
			////Everything Else
				} else {
					echo "<tr><td>".$fieldname."</td><td><form method=POST onsubmit='admin.php'>";
					echo "<input name= ".$fieldname." type=textarea value=".$value."><input type=submit value='Save' /></form></td></tr>";
				}
			}
			/* else {	
	////normal mode - link to edit mode
		////value name, value
				if ($fieldname == "FINANCE_USER"){
					echo "<tr><td>";
					echo "<a href=admin.php?edit=".$fieldname." title='Edit value for ".$fieldname."'>".$fieldname."</a></td>";
					echo "<td>".$userlist[$value]['FIRSTNAME']." ".$userlist[$value]['SURNAME']."</td></tr>";
				} else {
					echo "<tr><td><a href=admin.php?edit=".$fieldname." title='Edit value for ".$fieldname."'>".$fieldname."</a></td><td>".$value."</td></tr>";
				}
			}
			
		}*/
	}
	
}

//form the save sql
if ($savefield != "") {
	$sql = "UPDATE `Constants` SET `".$savefield."` = '".$savestring."' WHERE VERSIONNUMBER = '".$versionnumber."'";
	$sql = mysql_query($sql);
}
echo "</table><br /><br /><br />";



////Authorisers////
//ministry leader input
//note: userlist and authorisers at start of file

//reset the option lists for updated values
$authorisers = fetchAuthList();
$optionlist = createOptionList($userlist);

//check for saved authorisers and apply to database
foreach ($authorisers as $ministry => $guid) {
	$authorisers[$ministry] = array('GUID' => $guid, 'NAME' => $userlist[$guid]['FIRSTNAME']." ".$userlist[$guid]['SURNAME']);
	if($DEBUG)fb($ministry);
	if($DEBUG)fb(addslashes(str_replace(" ", "_", $ministry)));
	if (isset($_POST[addslashes(str_replace(" ", "_", $ministry))])) {		//if the ministry is set in POST (put there by a save action)
		$tempguid = $_POST[addslashes(str_replace(" ", "_", $ministry))];											//get the guid
		if($DEBUG)fb($tempministry);
		$sql = "UPDATE `Authorisers` SET `GUID` = '".$tempguid."' WHERE MINISTRY = \"".$ministry."\"";
		if($DEBUG)fb($sql);
		$sql = mysql_query($sql);
		echo "<br />".$userlist[$tempguid]['FIRSTNAME']." ".$userlist[$tempguid]['SURNAME']. " saved as ".$ministry." TMN authoriser!<br />";
		header("location=''");
	}
}

$authorisers = fetchAuthList();
$optionlist = createOptionList($userlist);
foreach ($authorisers as $ministry => $guid) {
	$authorisers[$ministry] = array('GUID' => $guid, 'NAME' => $userlist[$guid]['FIRSTNAME']." ".$userlist[$guid]['SURNAME']);
}
//note: optionlist at start of file

echo "<table border=1><th>Ministry:</th><th>Authoriser:</th>";
foreach ($authorisers as $ministry => $authuser) {
	echo "<tr><td>$ministry:</td><td>";
	echo "<form name='".$ministry."' method=POST onsubmit='admin.php'>";

	//find the location of the authorisers guid
	$personalcombo = split($authuser['GUID'], $optionlist);//($combobox, 0, strpos($combobox, $authuser['GUID'])+strlen($authuser['GUID']) + 1);
	if($DEBUG)fb($personalcombo);
	if($DEBUG)fb($authuser['GUID']);
	
	//select the authoriser in the combo box
	if ($personalcombo[1] != NULL) {
		echo "<select name='".htmlspecialchars($ministry, ENT_QUOTES)."'>";
			//optionlist with inserted "selected" parameter
			echo $personalcombo[0];
			echo $authuser['GUID'];
			echo "' selected";
			echo substr($personalcombo[1],1);
		echo "</select>";
	} else {
		if($DEBUG)fb("Authoriser ".$authuser['GUID']." not found");
		echo "<select name='".$ministry."'>";
		echo $optionlist;
		echo "<option value='".$authuser['GUID']."' selected>".$authuser['GUID']." - Name not in database! Never done a TMN?</option>";
		echo "</select>";
	}
	echo "<input type='submit' value='Save' /></form></td>";
	echo "</tr>";
}
echo "</table>";


echo "</body></html>";
?>














