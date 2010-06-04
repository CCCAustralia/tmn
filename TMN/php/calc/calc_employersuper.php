<?php

function calculateEmployerSuper($taxableincome){

	//TODO grab rate from DB
	$superrate = 0.09;

	return	$taxableincome * $superrate;
}

?>