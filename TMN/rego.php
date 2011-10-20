<?php
$html = '<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">' .
'<html>' .
	'<head>' .
		'<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">' .
		'<style type="text/css">	.body-look{		padding:10px;		border-color: #8db2e3;		background-color: #deecfd;		font: normal 14px tahoma,arial,helvetica;		color: #416aa3;	}	.title-look{		padding:6px;		background-image: url(lib/resources/images/default/panel/top-bottom.gif);		color:#15428b;		font:bold 14px tahoma,arial,verdana,sans-serif;	}	</style>' .
		'<title>User Not Found!</title>' .
	'</head>' .
	'<body>' .
		'<center>' .
			'<div class="title-look" style="position:relative;left:20px;width:608px;text-align:left;">User Not Found!</div>' .
			'<div class="body-look" style="position:relative;left:20px;width:600px;">You where not found in our system.<br />If you think you should be able to submit a TMN then register your details for processing.<br />Our Security checks usually take One buisness day to complete.</div>' .
		'</center>' .

		'<br />' .
		
		'<div class="title-look">Submit Details for Registration</div>' .
		'<form class="body-look" name="security_scan" action="security_scan.php" method="post">' .
			'<label for="firstname">First Name: </label><input type="text" name="firstname" value="' . phpCAS::getAttribute('firstName') . '" style="position:relative;left:120px;" readonly />' .
			'<br />' .
			'<label for="lastname">Last Name: </label><input type="text" name="lastname" value="' . phpCAS::getAttribute('lastName') . '" style="position:relative;left:121px;" readonly />' .
			'<br />' .
			'<label for="fan">Financial Account Number: </label><input type="text" name="fan" style="position:relative;left:26px;" />' .
			'<br />' .
			'<input type="submit" value="Submit" />' .
		'</form>' .
	'</body>' .
'</html>';

echo $html
?>