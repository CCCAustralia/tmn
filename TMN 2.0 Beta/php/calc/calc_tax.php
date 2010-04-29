<?php


//tax bands and rates for 2009-10

$bands_res = 	array(
					"band1_min"=>1,
					"band1_max"=>6000,
					"band1_rate"=>0,
					
					"band2_min"=>6001,
					"band2_max"=>35000,
					"band2_rate"=>0.15,
					
					"band3_min"=>35001,
					"band3_max"=>80000,
					"band3_rate"=>0.30,
					
					"band4_min"=>80001,
					"band4_max"=>180000,
					"band4_rate"=>0.38,
					
					"band5_min"=>180001,
					"band5_max"=>"unlimited",
					"band5_rate"=>0.45
				);
				
$bands_nonres = 	array(
					"band1_min"=>0,
					"band1_max"=>35000,
					"band1_rate"=>	0.29,
					
					"band2_min"=>	35001,
					"band2_max"=>	80000,
					"band2_rate"=>	0.30,
					
					"band3_min"=>	80001,
					"band3_max"=>	180000,
					"band3_rate"=>	0.38,
					
					"band4_min"=>	180001,
					"band4_max"=>	"unlimited",
					"band4_rate"=>	0.45
				);

/*			
//Calculate tax given a taxable income and resident-for-tax-purposes status
function calculateTax($taxableincome, $residency) {

	//Fetch the appropriate list of tax rates
	if ($residency == 'resident' || $residency == 'nonresident') {
		$residency = str_replace('resident','res',$residency);		//change the residency status from a readable form to a workable form
		$bands = $GLOBALS['bands_'.$residency];						//Fetch from global variables
	}
	else {
		return "Error: invalid residency parameter. Must be 'resident' or 'non-resident'.";		//if the residency status is in an unreadable form, return an error string
	}
	
	
	
	
	
	
	$tax = 0;	//initialise tax
	
	//echo "\ntaxableincome: ".$taxableincome;
	//Loop as many times as there are tax bands
	for ($i = 1; $i <= (count($bands)/3); $i++) {
		
		//echo "i: ".$i."\n";
		//if the taxableincome is in the band for this loop... (if the loop gets to the last band, it will continue regardless of upper limit 'unlimited')
		if ($taxableincome >= $bands['band'.$i.'_min'] && $taxableincome <= $bands['band'.$i.'_max'] || $bands['band'.$i.'_max'] == 'unlimited') {
			//echo "\nband $i _min: ".$bands_res['band'.$i.'_min']."\nband $i _max: ".$bands_res['band'.$i.'_max'];

			//calculate the tax on the amount of income in this band
			$tax += (($taxableincome - $bands['band'.($i - 1).'_max']) * $bands['band'.$i.'_rate']);
			//echo "\ntax(i=".$i.")=".$tax;

			//Loop through each band below the applicable band
			for ($j = $i - 1; $j != 0; $j = $j - 1) {
				
				//calculate the tax on the income in each subsequent band
				$t = (($bands['band'.($j).'_max'] - $bands['band'.($j - 1).'_max']) * $bands['band'.($j).'_rate']);
				//echo "\nt(j=".$j.")=".$t;
				$tax += $t;
			}
			break;	//make sure it doesn't loop again (if the correct band is found)
		}
	}	
	//round to nearest dollar - (half-up)
return round($tax, 0);
}

function getXband($x) {
	$bands = $GLOBALS['bands_res'];
	$xbands = getXband_arr();
	//print_r($xbands);
	
	$returnband = 0;
	
	foreach ($xbands as $k=>$v) {
		if ($x > $xbands[($k - 1)] && $x <= $v) {
			return $k;
			break;
		}
		if ($x > $xbands[($k - 1)] && $v == 'unlimited')
			return $k;
	}
}

function getXband_arr() {
	$bands = $GLOBALS['bands'.'_res'];
	
	$xbands = array();
	
	for ($i = 1; $i <= (count($bands)/3); $i++) {
		$b = $bands['band'.$i.'_max'];
		$j = ($b) - calculateTax($b, 'resident');
		if ($b == 'unlimited')
			$j = 'unlimited';
		$xbands[$i] = $j;
	}
	
	return $xbands;
}


function calculateTaxableIncome($x) {
	$bands = $GLOBALS['bands_'.'res'];
	$xbands = getXband_arr();
	
	$xband = getXband($x);
	//echo " xband: $xband";
	
	if ($xband == 1) {
		$tt = 0;
	}
	else {
		
		$xn = $x - $xbands[($xband-1)];
		//echo " xn: $xn";
		
		$rn = $bands['band'.$xband.'_rate'];
		//echo " rn: $rn";
		
		$tn = ($rn / (1-$rn)) * $xn;
		
		$tt = calculateTax($bands['band'.($xband - 1).'_max'], 'resident') + $tn;
	}
	
	$TI = $x + $tt;
	
	$tn = round($tn, 0);
	$tt = round($tt, 0);
	$TI = round($TI, 0);
	
	
	//echo " tn: $tn";
	//echo " tt: $tt";
	//echo " TI: $TI";
	//echo " TT: $tt";
	//echo "\n ".calculateTax($TI,'resident');
	
	
	//if ($TI < 0)
		//$TI = 0;
	
	return $TI;
}

//calculateTaxableIncome($_REQUEST['value']);
*/

//formula and values grabed from:
//Statement of formulas for calculating amounts to be withheld

//Scale 7 (Where payee not eligible to receive leave loading and has claimed tax-free threshold)
$x = array(
				198,
				342,
				402,
				576,
				673,
				1225,
				1538,
				3461,
				PHP_INT_MAX //this is the highest number possible
			);
			
$a = array(
				0.000,
				0.150,
				0.250,
				0.165,
				0.185,
				0.335,
				0.315,
				0.395,
				0.465
			);
			
$b = array(
				0.0000,
				29.7115,
				63.9308,
				29.7117,
				41.2502,
				142.2117,
				117.6925,
				240.7694,
				483.0771
			);
/*
function calculateTax($taxableincome, $residency) {
	//formula and values grabed from:
	//Statement of formulas for calculating amounts to be withheld
	$x = $GLOBALS['x'];
	$a = $GLOBALS['a'];
	$b = $GLOBALS['b'];
	
	//ATO rounding for monthly to weekly convertion (if $taxableincome ends with 33 cents then add one cent)
	if (($taxableincome-floor($taxableincome)) == 0.33) $taxableincome += 0.01;
	
	//convert from monthly to weekly
	$taxableincome = $taxableincome * 3 / 13; //same as $taxableincome = $taxableincome * 12 / 52
	
	//ATO rounding for weekly Tax calculation (ignore cents and add 0.99)
	$taxableincome = floor($taxableincome) + 0.99;
	
	//find which weekly tax bracket $taxableincome falls in
	for( $rangeCount = 0; $rangeCount < count($x); $rangeCount++ ){
		if ($taxableincome < $x[$rangeCount])
			break;
	}
	//calculate tax
	if ($rangeCount == 0)
		$tax = 0;
	else
		$tax = round($a[$rangeCount] * $taxableincome - $b[$rangeCount]);
	//convert back to monthly before returning
	return round($tax * 13 / 3); //same as $tax * 52 / 12
}*/

function calculateTaxableIncome($wage){
	return $wage + calculateTaxFromWage($wage, 'resident');
}


function calculateMaxWage($index) {
	//formula and values grabed from:
	//Statement of formulas for calculating amounts to be withheld
	$x = $GLOBALS['x'];
	$a = $GLOBALS['a'];
	$b = $GLOBALS['b'];
	
	//the max taxable income - the tax of max taxable income gives us the max wage for that tax bracket
	return $x[$index] - round($a[$index] * (floor($x[$index]) + 0.99) - $b[$index]);
}

function calculateTaxFromWage($wage, $residency) {
	//formula and values grabed from:
	//Statement of formulas for calculating amounts to be withheld
	$x = $GLOBALS['x'];
	$a = $GLOBALS['a'];
	$b = $GLOBALS['b'];
	
	//convert from months to weeks
	$wage = floor(floor($wage) * 12 / 52);
	
	for( $rangeCount = 0; $rangeCount < count($x); $rangeCount++ ){
		if ($wage < calculateMaxWage($rangeCount))
			break;
	}
	
	if ($rangeCount == 0)
		return 0;
	else
		return round(ceil(($a[$rangeCount] * ($wage) - $b[$rangeCount]) / (1 - $a[$rangeCount])) * 52 / 12);
}
/*
function calculateTaxableIncomeFromWage($wage, $residency) {
	
	return $wage + (calculateTaxFromWage($wage, $residency)) + 0.99;
}

//weekly
$ti_t = array(
	array(195, 0),
	array(196, 0),
	array(197, 0),
	array(198, 0),
	array(258,	9),
	array(259,	9),
	array(338,	21),
	array(339,	21),
	array(341,	22),
	array(342,	22),
	array(354,	25),
	array(355,	25),
	array(397,	36),
	array(398,	36),
	array(401,	37),
	array(402,	37),
	array(570,	65),
	array(571,	65),
	array(572,	65),
	array(573,	65),
	array(575,	65),
	array(576,	65),
	array(665,	82),
	array(666,	82),
	array(672,	83),
	array(673,	84),
	array(907,	162),
	array(908,	162),
	array(1218,	266),
	array(1219,	266),
	array(1220,	267),
	array(1221,	267),
	array(1224,	268),
	array(1225,	268),
	array(1531,	365),
	array(1532,	365),
	array(1537,	367),
	array(1538,	367),
	array(3143,	1001), 
	array(3144,	1002),
	array(3454,	1124),
	array(3455,	1124),
	array(3460,	1126),
	array(3461,	1127)
);
*//*
//monthly
$ti_t = array(
	array(845.00, 0),
	array(849.33, 0),
	array(853.67, 0),
	array(858.00, 0),
	array(1118.00, 39),
	array(1122.33, 39),
	array(1464.67, 91),
	array(1469.00, 91),
	array(1477.67, 95),
	array(1482.00, 95),
	array(1534.00, 108),
	array(1538.33, 108),
	array(1720.33, 156),
	array(1724.67, 156),
	array(1737.67, 160),
	array(1742.00, 160),
	array(2470.00, 282),
	array(2474.33, 282),
	array(2478.67, 282),
	array(2483.00, 282),
	array(2491.67, 282),
	array(2496.00, 282),
	array(2881.67, 355),
	array(2886.00, 355),
	array(2912.00, 360),
	array(2916.33, 364),
	array(3930.33, 702),
	array(3934.67, 702),
	array(5278.00, 1153),
	array(5282.33, 1153),
	array(5286.67, 1157),
	array(5291.00, 1157),
	array(5304.00, 1161),
	array(5308.33, 1161),
	array(6634.33, 1582),
	array(6638.67, 1582),
	array(6660.33, 1590),
	array(6664.67, 1590),
	array(13619.67, 4338),
	array(13624.00, 4342),
	array(14967.33, 4871),
	array(14971.67, 4871),
	array(14993.33, 4879),
	array(14997.67, 4884)
);
/*
for ( $testCount = 0; $testCount < count($x); $testCount++ ){
	$w = calculateMaxWage($testCount);
	$t = calculateTax($x[$testCount], 'resident');
	echo 'Taxable Income: '.$x[$testCount].'<br />';
	echo 'ATO Tax Value: '.$t.'<br />';
	echo 'ATO Wage Value: '.($x[$testCount] - $t).'<br />';
	echo 'Calculated Wage: '.$w.'<br />';
	
	if (abs($w - ($x[$testCount] - $t)) > 0)
		echo '<p style="color:red;">Diff: '. abs($w - ($x[$testCount] - $t)) . '</p>';
	else
		echo 'Diff: '. abs($w - ($x[$testCount] - $t)) . '<br /><br />';
}

for ( $testCount = 0; $testCount < count($ti_t); $testCount++ ){
	$cti = calculateTaxableIncome($ti_t[$testCount][0] - $ti_t[$testCount][1]);
	$ct = calculateTax($cti, 'resident');
	echo 'ATO Wage Value: '.($ti_t[$testCount][0] - $ti_t[$testCount][1]).'<br />';
	echo 'Calculated TI: '.$cti.'<br />';
	echo 'Calculated Tax: '.$ct.'<br />';
	echo 'ATO Taxable Income: '.$ti_t[$testCount][0].'<br />';
	echo 'ATO Tax Value: '.$ti_t[$testCount][1].'<br />';
	
	if (abs($cti - $ti_t[$testCount][0]) > 0.5){
		if ($cti > $ti_t[$testCount][0])
			echo '<p style="color:red;">Diff: '. abs($cti - $ti_t[$testCount][0]) . '</p>';
		else
			echo '<p style="color:red;">Diff: -'. abs($cti - $ti_t[$testCount][0]) . '</p>';
	}else{
		echo 'Diff: '. abs($cti - $ti_t[$testCount][0]) . '<br /><br />';
	}
}*/
?>