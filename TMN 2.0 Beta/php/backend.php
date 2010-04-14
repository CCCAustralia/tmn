<?php
$result = array(
"success"=>"true",
"data"=>array(
			"first" => "Tom",
			"last" => "Flynn",
			"dob" => "19/01/1987"
			)
);
						
						// Note that json_encode() wraps the data in [ ] and escapes slashes in dates, both of
						// which will cause problems in the Ext reader unless you make your own reader
						// The following hack is simply to demonstrate how you could get around this to return
						// the same format as the native reader is expecting. 
						// BUT a real example will need to cope with unexpected characters such as embedded 
						// double or single quotes etc
	$tmpData = json_encode($result);
	//$tmpData = substr($tmpData,1,strlen($tmpData)-2); // strip the [ and ]
	//$tmpData = str_replace("\\/","/",'{success:true,data:'.$tmpData.'}'); // unescape the slashes

	//$result = $tmpData;
	echo $tmpData; 
//$o = array('success'=>'true','first'=>'John','last'=>'Doe','dob'=>'yes');
//header("Content-Type: application/json");
//echo json_encode($o);
?>