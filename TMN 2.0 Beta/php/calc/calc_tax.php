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
	
	/*
	echo " tn: $tn";
	echo " tt: $tt";
	echo " TI: $TI";
	echo " TT: $tt";
	echo "\n ".calculateTax($TI,'resident');
	*/
	
	//if ($TI < 0)
		//$TI = 0;
	
	return $TI;
}

//calculateTaxableIncome($_REQUEST['value']);









?>