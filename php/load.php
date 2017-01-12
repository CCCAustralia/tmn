<?php
include_once("calc_tax.php");

if (isset($_COOKIE['NET_STIPEND']) && isset($_COOKIE['POST_TAX_SUPER'])){
	if (!isset($_COOKIE['ADDITIONAL_TAX'])) {
		setcookie('ADDITIONAL_TAX', 0, 0, '/TMN/');
	}
	$annum = ($_COOKIE['NET_STIPEND'] * 12) + ($_COOKIE['POST_TAX_SUPER'] * 12) + ($_COOKIE['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
	$TI = calculateTaxableIncome($annum);
	setcookie('TAXABLE_INCOME', round(($TI / 12), 0), 0,'/TMN/');
	
	$TAX = calculateTax($TI, 'resident');
    setcookie('TAX', round(($TAX / 12)), 0, '/TMN/');
}

if (isset($_COOKIE['S_NET_STIPEND']) && isset($_COOKIE['S_POST_TAX_SUPER'])){
	if (!isset($_COOKIE['S_ADDITIONAL_TAX'])) {
		setcookie('S_ADDITIONAL_TAX', 0, 0, '/TMN/');
	}
	$S_annum = ($_COOKIE['S_NET_STIPEND'] * 12) + ($_COOKIE['S_POST_TAX_SUPER'] * 12) + ($_COOKIE['S_ADDITIONAL_TAX'] * 12);	//calculate yearly figure
	$S_TI = calculateTaxableIncome($S_annum);
	setcookie('S_TAXABLE_INCOME', round(($S_TI / 12), 0), 0,'/TMN/');
	
	$S_TAX = calculateTax($S_TI, 'resident');
    setcookie('S_TAX', round(($S_TAX / 12), 0), 0, '/TMN/');
}

foreach ($_COOKIE as $k=>$v) {
	if ($v < 0 && $k != 'TAX' && $k != 'S_TAX') {
		$err .= "$k:\"Value cannot be negative.\", ";
	}
}

//echo $TI;
if ($err == '') {
	$result = array('success'=>'true');
	echo json_encode($result);
}
else {
		echo '{success: false, errors:{'.trim($err,", ").'} }';	//Return with errors
}
?>