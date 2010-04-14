<?php

function calculateAdditionalHousing($housing, $spouse){

	//TODO grab MAX HOUSING MFB from DB
	$maxhousingmfb = ($spouse ? 1600 : 960);
	
	return max( 0, $housing - $maxhousingmfb );
}

?>