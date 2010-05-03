<?php
$DEBUG = 1;

include_once("FinancialProcessor.php");

$financial_data = json_decode(stripslashes($_POST['financial_data']), true);
if($DEBUG && $financial_data == '') $financial_data = json_decode(stripslashes($_REQUEST['financial_data']), true);

$processor = new FinancialProcessor($financial_data, $DEBUG);

echo $processor->process();

?>