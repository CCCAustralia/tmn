<?php

$DEBUG = 1;


/*******************************************
#                                                             
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('lib/cas/cas.php');		//include the CAS module
//other includes
include_once('php/dbconnect.php');
include_once('php/FinancialSubmitter.php');
if($DEBUG) include_once("lib/FirePHPCore/fb.php");

if($DEBUG) ob_start();		//enable firephp logging
//eo other includes
//phpCAS::setDebug();			//Debugging mode
phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
phpCAS::setNoCasServerValidation();	//no SSL validation for the CAS server
phpCAS::forceAuthentication();		//require the user to log in to CAS


//user is now authenticated by the CAS server and the user's login name can be read with phpCAS::getUser()

//logout if desired
if (isset($_REQUEST['logout'])) {
	phpCAS::logout();
}


//fetch a ticket if absent
if ($_REQUEST['ticket'] == '' && $_REQUEST['id'] == '')
{
//echo GetMainBaseFromURL(curPageURL()). "<br />";
    header("Location: https://signin.mygcx.org/cas/login?service=".curPageURL());
}

$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
$xmlobject = new SimpleXmlElement($xmlstr);
$adminguid = $xmlobject->authenticationSuccess->attributes->ssoGuid;

if (!(($adminguid == '691EC152-0565-CEF4-B5D8-99286252652B') || ($adminguid == 'BD5326AF-8489-EA1C-40A4-CDCE009BA364') || ($adminguid == '3ED2AC1B-B145-7679-F82F-846E7C78DFF3') || ($adminguid == '967D4944-D93D-98E9-BEF4-59367B50F34C') || ($adminguid == '709D5953-2F3F-8484-0FA9-F96A242FF68F')))
	die("You don't have permission to access this!");



//check if they are a valid user (If not show the rego page)
$tmn_connection = db_connect();
$sql = mysql_query("SELECT JSON FROM Sessions WHERE SESSION_ID=".$_GET['session'].";", $tmn_connection);
if (mysql_num_rows($sql) == 1){

	$row = mysql_fetch_assoc($sql);
	$returntext = $row['JSON'];
	
	
	if (isset($_REQUEST['r']) && $_REQUEST['r'] == 1){
		$up = $row['JSON'];
		
		$obj = json_decode($up, true);
		
		
		//convert to uppercase
		foreach($obj['tmn_data'] as $key => $value){
				$upobj[strtoupper($key)] = $value;
		}
		
		//convert to int if possible
		foreach($upobj as $key => $value){
			if (is_int((int)$value))
				$upobj[$key] = (int)$value;
		}

		$guidrow = mysql_fetch_assoc(mysql_query("SELECT GUID FROM User_Profiles WHERE FIN_ACC_NUM=".$obj['tmn_data']['fan'].";"));
		$guid = $guidrow['GUID'];
		
		//add guid to upobj (to be passed to financial submitter
		$upobj['guid'] = $guid;
	
		$reprocessor = new FinancialSubmitter($upobj, $DEBUG);
		$returntext = $reprocessor->submit();
		
		
	}

		$returntext = str_replace("'", "\'", $returntext);
//echo $returntext;

	//display tmn
	
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css" /><title>TMN - Reprocessor</title></head><body><script type="text/javascript" src="lib/ext-base.js"></script><script type="text/javascript" src="lib/ext-all.js"></script><script type="text/javascript" src="lib/Printer-all.js"></script><script type="text/javascript" src="ui/view_tmn.js"></script><script type="text/javascript">Ext.onReady(function(){var reprocessor = new Ext.Panel({title: "Reprocessor",frame: true,renderTo: "cont",items: [{xtype: "view_tmn"}]});reprocessor.items.items[0].response = \''.$returntext.'\';reprocessor.items.items[0].loadForm();var print = new Ext.Button({renderTo: "btn",text: "Print",scope: reprocessor,handler: function(){if (Ext.isChrome){window.print();} else{Ext.ux.Printer.print(reprocessor);}}});});</script><center><div id="cont"></div><div id="btn"></div></center></body></html>';
	//Ext.onReady(function(){var reprocessor = new Ext.Panel({title: "Reprocessor",frame: true,renderTo: "cont",listeners:{afterrender: function(panel){var returnObj = JSON.parse(\''.$returntext.'\');var values = returnObj["tmn_data"];var single_tpl = new Ext.XTemplate(\''.$single_template.'\');var spouse_tpl = new Ext.XTemplate(\''.$spouse_template.'\');if (values["s_firstname"] == null){single_tpl.overwrite(panel.body, values);} else {spouse_tpl.overwrite(panel.body, values);}}},hand: function(){if (Ext.isChrome){window.print();}else{Ext.ux.Printer.print(this);}}});var print = new Ext.Button({renderTo: "btn",text: "Print",scope: reprocessor,handler: reprocessor.hand});});
	

} else {
	echo "not found";	
}

function curPageURL() {
	$pageURL = 'http';
	if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}

?>