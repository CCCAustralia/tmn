<?php


/*******************************************                                                        
# Test Code                 
*******************************************/

include_once('../classes/Tmn.php');
include_once('../classes/TmnCrud.php');
$LOGFILE	= "../logs/TmnCrudClass-test.log";
$DEBUG		= 1;

	//Constructor test
	
fb("Constructor Test");
try {
	$tmn			= new Tmn($LOGFILE);
	$crud			= new TmnCrud(
			$LOGFILE,
			"Low_Account",				//name of table
			"fin_acc_num",							//name of table's primary key
			array(							//an assoc array of private field names and there types
				'id'					=>	"i"
			),
			array(							//an assoc array of public field names and there types
				'fin_acc_num'			=>	"i",
				'current_session_id'	=>	"i",
				'tmn_effective_date'	=>	"s",
				'consecutive_low_months'=>	"i",
				'pinkslip_exemption'	=>	"i",
				'mpd_plan'				=>	"i",
				'restrict_mfbmmr'		=>	"i"
			)
	);
} catch (Exception $e) {
	Reporter::newInstance($LOGFILE)->exceptionHandler($e);
}


/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * [<now>] User Authenticated: guid = 691EC152-0565-CEF4-B5D8-************
 * 
 * Screen Output:
 * 
 */

	//JSON test
fb("JSON Test");
$assocArray	=	array(
		'fin_acc_num'			=>	1011341,
		'current_session_id'	=>	20000,
		'tmn_effective_date'	=>	"3000-01-01",
		'consecutive_low_months'=>	1200,
		'pinkslip_exemption'	=>	1,
		'mpd_plan'				=>	1,
		'restrict_mfbmmr'		=>	0
);

try {
	fb("loadDataFromAssocArray(assocArray): " . $crud->loadDataFromAssocArray($assocArray));
	fb("createOrUpdateIfExists(): " . $crud->createOrUpdateIfExists());
} catch (Exception $e) {
	$crud->exceptionHandler($e);
}

/*
 * Expected output
 * 
 * Console Output:
 * Constructor Test
 * [2011/03/22 03:52:26] User Authenticated: guid = 691EC152-0565-CEF4-B5D8-************
 * JSON Test
 * loadDataFromAssocArray(assocArray):
 * produceJson(): {"firstname":"my","surname":"self","ministry":"StudentLife","ft_pt_os":1,"fin_acc_num":1010000}
 * create():
 * reset():
 * produceJson(): []
 * loadDataFromJsonString('{data:{guid:testtesttest}}'):
 * retrieve():
 * produceJson(): {"firstname":"my","surname":"self","ministry":"StudentLife","ft_pt_os":1,"fin_acc_num":1010000}
 * loadDataFromAssocArray(assocArray):
 * update():
 * reset():
 * produceJson(): []
 * loadDataFromJsonString('{data:{guid:testtesttest}}'):
 * retrieve():
 * produceJson(): {"firstname":"my","surname":"self","ministry":"StudentLife","ft_pt_os":1,"days_per_week":3,"fin_acc_num":1010000,"admin_tab":1}
 * delete():
 * produceJson(): []
 * [2011/03/22 03:52:26] /Users/michaelharrison/Documents/IT/code/TMN/Eclipse/tmn/trunk/TMN/php/TmnCrud.php; ln 140; Light Exception; TmnCrud Exception: On Retrieve, User Not Found
 * /Users/michaelharrison/Documents/IT/code/TMN/Eclipse/tmn/trunk/TMN/php/TmnCrud.php; ln 140; Light Exception; TmnCrud Exception: On Retrieve, User Not Found
 * 
 * Screen Output:
 * 
 */

?>
