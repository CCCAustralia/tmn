<?php

$DEBUG = 0;


/*******************************************
#                                                             
# index.php - Generic index for PHP GCX SSO login                    
*******************************************/

//GCX login
include_once('lib/cas/cas.php');		//include the CAS module
//other includes
include_once('php/dbconnect.php');
if($DEBUG) require_once("lib/FirePHPCore/fb.php");
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

if($DEBUG) ob_start();		//enable firephp logging


//check if they are a valid user (If not show the rego page)
$tmn_connection = db_connect();
$sql = mysql_query("SELECT SESSION_ID, SESSION_NAME FROM Sessions;", $tmn_connection);
$row = mysql_fetch_assoc($sql);
for ($count=0; $count < mysql_num_rows($sql); $count++){
	$sessiontext .= '['.$row['SESSION_ID'].', "'.$row['SESSION_NAME'].'"], ';
	$row = mysql_fetch_assoc($sql);
}
$sessiontext = trim($sessiontext, ", ");

	//display tmn
	echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css"/><title>TMN - Reprocessor</title></head><body><script type="text/javascript" src="lib/ext-base.js"></script><script type="text/javascript" src="lib/ext-all.js"></script><script type="text/javascript" src="lib/Printer-all.js"></script><script type="text/javascript">function getScrollWidth(){var w = window.pageXOffset ||document.body.scrollLeft ||document.documentElement.scrollLeft;return w ? w : 0;}function getDocHeight() {var D = document;return Math.max(Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),Math.max(D.body.clientHeight, D.documentElement.clientHeight));}Ext.onReady(function(){var reprocessor = new Ext.Panel({id: "rep",title: "Reprocessor",frame: true,renderTo: "cont",items: [{id:"com",xtype: "combo",fieldLabel: "Select the session you want to view",name: "SESSION",hiddenName: "SESSION",hiddenId: "SESSION_hidden",triggerAction:"all",editable: false,mode:"local",store:new Ext.data.SimpleStore({fields:["sessionCode", "sessionName"],data:['.$sessiontext.']}),displayField:"sessionCode",valueField:"sessionCode",tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{sessionCode}, {sessionName}</div></tpl>",listeners: {select: function(combo, record, index) {if (Ext.getCmp("check").pressed) {document.cookie = "r=1; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + record.get("sessionCode")+"&ticket='.$_COOKIE['ticket'].'";} else {document.cookie = "r=0; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + record.get("sessionCode")+"&ticket='.$_COOKIE['ticket'].'";}document.getElementById("reprocess_frame").width = Ext.getBody().getSize().width - getScrollWidth() - 5;document.getElementById("reprocess_frame").height = Ext.getBody().getSize().height - Ext.getCmp("rep").getHeight() - 5;}}},{id: "check",xtype: "button",enableToggle: true,text: "View this data having been reprocessed with the lastest version of the TMN?",toggleHandler: function(checkbox, checked) {if (Ext.getCmp("com").getValue() > 0){if (checked) {document.cookie = "r=1; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + Ext.getCmp("com").getValue()+"&ticket='.$_COOKIE['ticket'].'";} else {document.cookie = "r=0; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + Ext.getCmp("com").getValue()+"&ticket='.$_COOKIE['ticket'].'";}document.getElementById("reprocess_frame").width = Ext.getBody().getSize().width - getScrollWidth() - 5;document.getElementById("reprocess_frame").height = Ext.getBody().getSize().height - Ext.getCmp("rep").getHeight() - 5;}}}]});var print = new Ext.Button({renderTo: "btn",text: "Print",scope: reprocessor,handler: reprocessor.hand});});</script><center><div id="cont"></div><iframe id="reprocess_frame" src=""></iframe></center></body></html>';
	//echo '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\"><html><head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="lib/resources/css/ext-all.css"/><title>TMN - Reprocessor</title></head><body><script type="text/javascript" src="lib/ext-base.js"></script><script type="text/javascript" src="lib/ext-all.js"></script><script type="text/javascript" src="lib/Printer-all.js"></script><script type="text/javascript">function getScrollWidth(){var w = window.pageXOffset ||document.body.scrollLeft ||document.documentElement.scrollLeft;return w ? w : 0;}function getDocHeight() {var D = document;return Math.max(Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),Math.max(D.body.clientHeight, D.documentElement.clientHeight));}Ext.onReady(function(){var reprocessor = new Ext.Panel({id: "rep",title: "Reprocessor",frame: true,renderTo: "cont",items: [{id:"com",xtype: "combo",fieldLabel: "Select the session you want to view",name: "SESSION",hiddenName: "SESSION",hiddenId: "SESSION_hidden",triggerAction:"all",editable: false,mode:"local",store:new Ext.data.SimpleStore({fields:["sessionCode", "sessionName"],data:['.$sessiontext.']}),displayField:"sessionCode",valueField:"sessionCode",tpl: "<tpl for=\".\"><div class=\"x-combo-list-item\">{sessionCode}, {sessionName}</div></tpl>",listeners: {select: function(combo, record, index) {if (Ext.getCmp("check").getValue()) {document.cookie = "r=1; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + record.get("sessionCode")+"&ticket='.$_COOKIE['ticket'].'";} else {document.cookie = "r=0; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + record.get("sessionCode")+"&ticket='.$_COOKIE['ticket'].'";}document.getElementById("reprocess_frame").width = Ext.getBody().getSize().width - getScrollWidth() - 5;document.getElementById("reprocess_frame").height = Ext.getBody().getSize().height - Ext.getCmp("rep").getHeight() - 5;}}},{id: "check",xtype: "checkbox",boxLabel: "Do you want this session to be reprocessed with the lastest version of the TMN?",listeners: {check: function(checkbox, checked) {if (Ext.getCmp("com").getValue() > 0){if (checked) {document.cookie = "r=1; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + Ext.getCmp("com").getValue()+"&ticket='.$_COOKIE['ticket'].'";} else {document.cookie = "r=0; path=\"/TMN/\"";document.getElementById("reprocess_frame").src="reprocess.php?session=" + Ext.getCmp("com").getValue()+"&ticket='.$_COOKIE['ticket'].'";}document.getElementById("reprocess_frame").width = Ext.getBody().getSize().width - getScrollWidth() - 5;document.getElementById("reprocess_frame").height = Ext.getBody().getSize().height - Ext.getCmp("rep").getHeight() - 5;}}}}]});var print = new Ext.Button({renderTo: "btn",text: "Print",scope: reprocessor,handler: reprocessor.hand});});</script><center><div id="cont"></div><iframe id="reprocess_frame" src=""></iframe></center></body></html>';

function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

?>