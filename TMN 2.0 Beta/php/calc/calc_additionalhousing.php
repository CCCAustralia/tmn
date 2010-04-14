<?php

function calculateAdditionalHousing($housing, $freq, $spouse){

	//TODO grab MAX HOUSING MFB from DB
	$maxhousingmfb = ($spouse ? 1600 : 960);
	
	if ($freq == 1) $housing = $housing * 26 / 12;
	
	return max( 0, $housing - $maxhousingmfb );
}

?>