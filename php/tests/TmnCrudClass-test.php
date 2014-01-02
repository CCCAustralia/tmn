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
			"User_Profiles",				//name of table
			"guid",							//name of table's primary key
			array(							//an assoc array of private field names and there types
				'guid'		=>	"s"
			),
			array(							//an assoc array of public field names and there types
				'firstname'		=>	"s",
				'surname'		=>	"s",
				'spouse_guid'	=>	"s",
				'ministry'		=>	"s",
				'ft_pt_os'		=>	"i",
				'days_per_week'	=>	"i",
				'fin_acc_num'	=>	"i",
				'mpd'			=>	"i",
				'm_guid'		=>	"s",
				'admin_tab'		=>	"i"
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
$guidArray	=	array('data' => array('guid' => "testtesttest"));
$assocArray	=	array(
		'guid'			=>	"testtesttest",
		'firstname'		=>	"my",
		'surname'		=>	"self",
		'spouse_guid'	=>	null,
		'ministry'		=>	"StudentLife",
		'ft_pt_os'		=>	1,
		'days_per_week'	=>	null,
		'fin_acc_num'	=>	1010000,
		'mpd'			=>	null,
		'm_guid'		=>	null,
		'admin_tab'		=>	null
);

try {
	fb("loadDataFromAssocArray(assocArray): " . $crud->loadDataFromAssocArray($assocArray));
	fb("produceJson(): " . $crud->produceJson());
	fb("create(): " . $crud->create());
	fb("reset(): " . $crud->reset());
	fb("produceJson(): " . $crud->produceJson());
	fb("loadDataFromJsonString('{data:{guid:testtesttest}}'): " . $crud->loadDataFromJsonString(json_encode($guidArray)));
	fb("retrieve(): " . $crud->retrieve());
	fb("produceJson(): " . $crud->produceJson());
	$assocArray['days_per_week'] = 3;
	$assocArray['admin_tab'] = 1;
	fb("loadDataFromAssocArray(assocArray): " . $crud->loadDataFromAssocArray($assocArray));
	fb("update(): " . $crud->update());
	fb("reset(): " . $crud->reset());
	fb("produceJson(): " . $crud->produceJson());
	fb("loadDataFromJsonString('{data:{guid:testtesttest}}'): " . $crud->loadDataFromJsonString(json_encode($guidArray)));
	fb("retrieve(): " . $crud->retrieve());
	fb("produceJson(): " . $crud->produceJson());
	fb("delete(): " . $crud->delete());
	fb("produceJson(): " . $crud->produceJson());
	fb("retrieve(): " . $crud->retrieve());
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
