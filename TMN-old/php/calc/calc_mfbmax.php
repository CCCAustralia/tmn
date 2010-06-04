<?php

function calculateMaxMFB($taxableincome, $mfbrate, $daysperweek) {
	
	$maxmfb = $taxableincome;
	
	$maxmfb = $maxmfb * ($mfbrate);
	
	$maxmfb = $maxmfb * ($daysperweek / 5);
	
	return $maxmfb;
}











































?>