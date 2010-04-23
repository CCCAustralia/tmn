<?php
$DEBUG = 1;

include_once("financial_processor.php");

$financial_data = json_decode(stripslashes($_POST['financial_data']), true);
if($DEBUG && $financial_data == '') $financial_data = json_decode(stripslashes($_REQUEST['financial_data']), true);

$fin_proc = new finproc($financial_data, $DEBUG);

echo $fin_proc->proc();

?>