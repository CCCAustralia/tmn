<?php
//Authenticate the user in GCX with phpCAS
include_once('../lib/cas/cas.php');		//include the CAS module
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'signin.mygcx.org',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('You are not authorised to access this page. If you think you should have access, please contact <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>');

if (isset($_SESSION['phpCAS'])) {
	$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
	$xmlobject = new SimpleXmlElement($xmlstr);
	$users_guid = $xmlobject->authenticationSuccess->attributes->ssoGuid;
}

/*******************************************
#                                                                         #
# tmn.php - print it page                                       #
#                                                                        #
*******************************************/

//get id from guid
$db_name ="mportal_tmn";
$connection = @mysql_connect("localhost", "mportal","***REMOVED***") or die(mysql_error());
$db = @mysql_select_db($db_name,$connection) or die(mysql_error());
$sql= mysql_query("SELECT FIN_ACC_NUM FROM User_Profiles WHERE GUID='".$users_guid."'");
$row= mysql_fetch_array($sql);

// convert id from 101#### to 800#### numbers
if ($row['FIN_ACC_NUM'] >= 8000000 && $row['FIN_ACC_NUM'] < 9000000) {
	$id=$row['FIN_ACC_NUM'];
} else {
	$id=$row['FIN_ACC_NUM'] + 6990000;
}



// connect to sql db
$db_name ="student_student";
$connection = @mysql_connect("localhost","student_student","okbrain15") or die(mysql_error());
$db = @mysql_select_db($db_name,$connection) or die(mysql_error());



// check that id matches cookie - otherwise send him to login page with error
//if ($id!=$_COOKIE[ccca_id] OR $id==''){
//header("Location:http://www.studentlife.org/TMN/index.php?id=wrong");
//exit();
//}


// set mgl_approv to null
$mgl_approv='';


$sql= mysql_query("SELECT * FROM cccatmn where CCCA_Account='".$id."'");
//if your last tmn is not found quit
if(mysql_num_rows($sql) == 0){
	die("Your Last TMN could not be found. Try refering to your printed version.");
}
$row= mysql_fetch_array($sql);


// get name ready for print-off
$name = '';
$name1 = $name.$row[Name1]." ";
if ($row[Name2]!=''){
	$name1 = $name1."& ".$row[Name2]." ";
}
$name = $name1.$row[Surname];

if ($row[Name2]!=''){
	$name_note = $name." have";
} else {
$name_note = $name." has";
}

// Calculate Child and Educational allowance figures
$allowances=($row[child_allowance]+$row[education_allowance]);

if ($row[Name2]==""){
$allow1=$allowances;
} else {
$allow1=round($allowances/2,2);
}
$allow2=$allowances-$allow1;

// if not test account or already printed
if(($id!='4350' || $id=='2009') AND $row[printed]!='y'){


// email MC Admin if level of Life Cover changes - one for each partner
$cover1=round(($row[Additional_Life_Cover_1]*(3/13)),2);
if (($cover1-$row[Add_Life_Cover1])!='0'){
$to="mc.admin@ccca.org.au";
$headers='From:'.$row[Email];
$subject="Additional Life Cover Change for ".$row[Name1]." ".$row[Surname];
$body="Dear Member Care,

I am wanting to change my Additional Life Cover from $".$row[Add_Life_Cover1]." per week to $".$cover1." per week.

Thanks,
".$row[Name1]." ".$row[Surname];
mail($to, $subject, $body, $headers);
}

$cover2=round(($row[Additional_Life_Cover_2]*(3/13)),2);
if (($cover2-$row[Add_Life_Cover2])!='0'){
$to="mc.admin@ccca.org.au";
$headers='From:'.$row[Email];
$subject="Additional Life Cover Change for ".$row[Name2]." ".$row[Surname];
$body="Dear Member Care,

I am wanting to change my Additional Life Cover from $".$row[Add_Life_Cover2]." per week to $".$cover2." per week.

Thanks,
".$row[Name2]." ".$row[Surname];
mail($to, $subject, $body, $headers);
}

if (($row[email_payroll]>2) && ($row[posiiton1]=="Overseas")){

// check if payroll needs an email to send them TFN Declaration forms
if (!is_int($row[email_payroll]/2)){

$to="payroll@ccca.org.au";

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: Payroll <payroll@ccca.org.au>' . "\r\n";
$headers .= 'From: '.$row[email] . "\r\n";
$headers .= 'Cc: mc.admin@ccca.org.au, philip.goh@ccca.org.au' . "\r\n";

$subject="Tax Status change for ".$name;
$body="
<p>Dear Payroll,<br>&nbsp;<br>

".$name_note." requested a change in their tax status from ".$row[prev_tax_stat]." to ".$row[tax_stat]." via their TMN form. Please send them a Tax File Number Declaration Form.<br>Please do not enact this change until the TFN Declaration form has been returned.<br>&nbsp;<br>
 
Yours,<br>
 
The CCCA Technology Team</p>";
mail($to, $subject, $body, $headers);


$to=$row[Email];

// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

// Additional headers
$headers .= 'To: Payroll <payroll@ccca.org.au>' . "\r\n";
$headers .= 'From: '.$row[email] . "\r\n";
$headers .= 'Cc: mc.admin@ccca.org.au, philip.goh@ccca.org.au' . "\r\n";

$subject="Tax Status change for ".$name;
$body="<p>
Dear ".$name1.",<br>&nbsp;<br>
 
You have requested a change in your tax status from ".$row[prev_tax_stat]." to ".$row[tax_stat]." via the TMN form. Finance has been notified of your request and will be sending you a Tax File Number Declaration Form soon.<br>&nbsp;<br>
 
Please note that your tax status will not change until you have completed this form, sent it to Finance and it is processed by them.<br>&nbsp;<br>
 
Yours,<br> 
The IT Team</p>";
mail($to, $subject, $body, $headers);



} else {

$to=$row[Email];
$headers='From:'.$row[Email];
$subject="No forms required for ".$name;
$body="
Dear ".$name1.",
 
You changed your tax status on the TMN form a number of times. In the end, your tax status remained as ".$row[tax_stat]."; therefore you do not need to submit any forms.
 
If you did intend to change your tax status, then please log on and record your desired change on the TMN form.
 
Yours,<br>
 
The CCCA Technology Team</p>";

mail($to, $subject, $body, $headers);

}

}

} // endif test account & printed



// work out gross stipend again

$gross1=$row[Tax_Inc_1]-$row[Adtnl_Tax_1]-$row[Additional_Super_1];
$gross2=$row[Tax_Inc_2]-$row[Adtnl_Tax_2]-$row[Additional_Super_2];



// housing stuff again
if ($row[Rent_Costs]!='0.00'){
	$housing=$row[Rent_Costs];
} else {
	$housing=$row[Mortgage_Costs];
}

if ($row[Housing_Freq]=='Fortnightly'){
	$housing=round($housing/(2+1/6), 2);
$AHA=round($row[Add_House]/(2+1/6),2);
}else {
$AHA=$row[Add_House];
}

// tell payroll how much work is being done
if ($row[position1]=='PT'){
$position="Part Time";
if ($row[Name2]==''){
if ($row[Hours_Working_1]=='1/2 Day'){
$j='1';
}
if ($row[Hours_Working_1]=='1 Day'){
$j='2';
}
if ($row[Hours_Working_1]=='2 Days'){
$j='4';
}
if ($row[Hours_Working_1]=='3 Days'){
$j='6';
}
if ($row[Hours_Working_1]=='4 Days'){
$j='8';
}

} else {

if ($row[Hours_Working_1]=='1 Day'){
$j='1';
}
if ($row[Hours_Working_1]=='2 Days'){
$j='2';
}
if ($row[Hours_Working_1]=='3 Days'){
$j='3';
}
if ($row[Hours_Working_1]=='4 Days'){
$j='4';
}
if ($row[Hours_Working_1]=='5 Days'){
$j='5';
}
if ($row[Hours_Working_1]=='6 Days'){
$j='6';
}
if ($row[Hours_Working_1]=='7 Days'){
$j='7';
}
if ($row[Hours_Working_1]=='8 Days'){
$j='8';
}

}
} else {
$j=10;
if ($row[position1]=='FT'){
$position="Full Time";
} else {
$position="Overseas";
}
}



$sql= mysql_query("SELECT * FROM cccatmn where CCCA_Account='".$id."'");
$row= mysql_fetch_array($sql);



if ($row[position1]=="Overseas"){
	$add_tax1=round(($row[Home_TI1]-$row[Tax_Inc_1])*($row[no_months]/12),0);
	$add_tax2=round(($row[Home_TI2]-$row[Tax_Inc_2])*($row[no_months]/12),0);
	if($row[MFB_1]>$row[LAFHA_1]){
		$add_mfb1=round(($row[MFB_1]-$row[LAFHA_1])*($row[no_months]/12),0);
	}else{
		$add_mfb1=0;
	}
	if ($row[MFB_2]>$row[LAFHA_2]){
		$add_mfb2=round(($row[MFB_2]-$row[LAFHA_2])*($row[no_months]/12),0);
	}else{
		$add_mfb2=0;
	}
	$oha=round(($row[OHA])*($row[no_months]/12),0);
	
	if ($row[Name2]!=''){
		$oha1=($oha/2);
	} else {
		$oha1=$oha;
	}
	
	
	$additional1=round(($add_tax1+$add_mfb1+$oha1),0);
	$additional2=round(($add_tax2+$add_mfb2+$oha-$oha1),0);
	
	
}






// get date ready for print-off
$today=date("j M Y");

// check if any extra authorisation is needed
if ($row[Add_House]>'0'){
	$authorise='y';
	$reason="you have claimed Additional Housing Allowance";
}


if($row[education_allowance]!="0"){
		$authorise='y';
		if (isset($reason)){
			$reason.=" and you have claimed Education Allowance";
		} else {
			$reason="you have claimed Education Allowance";
		}
}


if ($row[Name2]!=''){
	if ($row[TMN] < $j*'350' || $row[TMN] > $j*'700'){  //THIS NEEDS TO BE UPDATED AS TMN RECOMMENDED BANDS CHANGE
		$authorise='y';
		if (isset($reason)){
			$reason.=" and your TMN is outside the Recommended Band";
		} else {
			$reason="your TMN is outside the Recommended Band";
		}
	}
} else {
	if ($row[TMN] < $j*'230' || $row[TMN] > $j*'400'){  //THIS NEEDS TO BE UPDATED AS TMN RECOMMENDED BANDS CHANGE
		$authorise='y';
		if (isset($reason)){
			$reason.=" and your TMN is outside the Recommended Band";
		} else {
			$reason="your TMN is outside the Recommended Band";
		}
	}
}


// set up MGL approval stuff

if ($row[Name2]==""){
$fp_guide=3850; // 110% of mfb's fp_guide
} else {
$fp_guide=6380; // 110% of mfb's fp_guide
}

$joint=$row[Fin_Pack_1]+$row[Fin_Pack_2];

if ($joint-(($j*$fp_guide)/10)>'0'){
$mgl_approv='y'; // set this so it asks for MGL approval down the bottom
} else { // for NML only approval

if ($row[extra_auth]=="y"){
	$authorise='y';
	if (isset($reason)){
if ($row[Name2]==""){
		$reason.=" and your Financial Package is outside the FP Limits";
} else {
		$reason.=" and your Joint Financial Package is outside the FP Limits";
}

	} else {
if ($row[Name2]==""){
		$reason="your Financial Package is outside the FP Limits";
} else {
		$reason="your Joint Financial Package is outside the FP Limits";
}
	}
}


}






// use different wording for overseas people with housing allowance
if ($row[position1]!="Overseas"){
$house_state="Housing from Support Account";
} else {
$house_state="Overseas Housing Allowance (to be claimed on Form I-7B)";
$housing=$row[OHA];
if($row[no_months]!="0"){
$housing = $housing." per month for ".$row[no_months]." month/s";
}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Total Monthly Needs for <?php echo($name);?></title>
<style>
body {
	font-family: Arial, Helvetica, sans-serif;
font-size:12px;
}


table {
	font-family: Arial, Helvetica, sans-serif;
font-size:12px;
}
</style>


</head>

<body style="background-color:#ffffff;">
<table width="100%"><tr><td>
<h2>Total Monthly Needs/ Periodic Payments</h2>
</td>
<?php if($row[position1]!="Overseas"){ ?>
<td align="right">
<img src="http://www.ccca.org.au/tntmpd/TntMPDCCCALogo-new.JPG" alt="CCCA Logo" width="150" height="50"></td>
<?php } ?>
</tr>
</table>
<p>&nbsp;</p>


<table width="90%"><tr><td width="30%"><?php echo("<strong>Name:</strong> ".$name);?></td><td width="30%"><?php echo("<strong>Date:</strong> ".$today);?></td><td width="40%"><?php echo("<strong>Support Account:</strong> ".$id."</td></tr></table>"); ?>

<p><?php if($row[position1]=="Overseas"){ ?><strong>Overseas Worker</strong><br><?php } else { ?>&nbsp;<?php } ?></p>

<table width="100%">
  <tr>
    <th colspan="2" scope="col">&nbsp;</th>
    <th scope="col" width="17%">Partner 1 </th>
    <th scope="col" width="26%">Partner 2 </th>
  </tr>
  <tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Name</th>
    <td align="center"><?php echo($row[Name1]);?></td>
    <td align="center"><?php echo($row[Name2]);?></td>
  </tr>
<tr>
    <td colspan="4"><hr></td>
  </tr>
<tr>
    <th colspan="2" scope="row">Ministry</th>
    <td colspan="2" align="center"><?php echo($row[IM]);?></td>

</tr>

<tr>
    <td colspan="4"><hr></td>
  </tr>




<?php if ($row[position1]!="Overseas"){ ?>
  <tr>
    <th colspan="2" scope="row">Status</th>
    <td colspan="2" align="center"><?php echo($position); if($row[position1]=='PT'){ echo(" - ".$row[Hours_Working_1]);  if($row[Name2]!=''){echo(" as a couple.");}}?></td>
  </tr>
<?php } else { ?>
  <tr>
    <th colspan="2" scope="row">Tax Status</th>
    <td colspan="2" align="center"><?php echo($row[tax_stat]);?></tr>
<?php } ?>
<tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Gross Stipend </th>
    <td align="center">$<?php echo($gross1);?></td>
    <td align="center">$<?php echo($gross2);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Additional Tax </th>
    <td align="center">$<?php echo($row[Adtnl_Tax_1]);?></td>
    <td align="center">$<?php echo($row[Adtnl_Tax_2]);?></td>
  </tr>
<?php if($row[tax_stat]!="Non-resident"){ ?>
  <tr>
    <th colspan="2" scope="row">Post-tax Super </th>
    <td align="center">$<?php echo($row[Additional_Super_1]);?></td>
    <td align="center">$<?php echo($row[Additional_Super_2]);?></td>
  </tr>
<?php } ?>

  <tr>
    <td colspan="4"><hr></td>
  </tr>


  <tr>
    <th colspan="2" scope="row">Taxable Income (Gross Stipend + Additional Tax<?php if($row[tax_stat]!="Non-resident"){?> + Post-Tax Super<?php } ?>)</th>
    <td align="center">$<?php echo($row[Tax_Inc_1]);?></td>
    <td align="center">$<?php echo($row[Tax_Inc_2]);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Pre-tax Super </th>
    <td align="center">$<?php echo($row[Super_Pretax_1]);?></td>
    <td align="center">$<?php echo($row[Super_Pretax_2]);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Additional Life Cover (per month)</th>
    <td align="center">$<?php echo($row[Additional_Life_Cover_1]);?></td>
    <td align="center">$<?php echo($row[Additional_Life_Cover_2]);?></td>
  </tr>
<?php if($row[position1]!="Overseas"){ ?>
  <tr>
    <th colspan="2" scope="row">MFB's</th>
    <td align="center">$<?php echo($row[MFB_1]);?></td>
    <td align="center">$<?php echo($row[MFB_2]);?></td>
  </tr>
<tr>
    <th colspan="2" scope="row">MFB Claim Rate</th>
    <td colspan="2" align="center"><?php echo($row[MFB_Rate]);?></td>
  </tr>
<?php } else { ?>
  <tr>
    <th colspan="2" scope="row">LAFHA's</th>
    <td align="center">$<?php echo($row[LAFHA_1]);?></td>
    <td align="center">$<?php echo($row[LAFHA_2]);?></td>
  </tr>
<?php if($row[tax_stat]=="Non-resident"){ ?>
  <tr>
    <th colspan="2" scope="row">Super Top Up</th>
    <td align="center">$<?php echo($row[top_up1]);?></td>
    <td align="center">$<?php echo($row[top_up2]);?></td>
  </tr>
<?php } ?>
  
<tr>
<th colspan="2" scope="row">Amount allowed for added tax, Overseas Housing Allowance and MFB while on Home Assignment</th>
<td align="center">$<?php echo($additional1);?></td>
<td align="center">$<?php echo($additional2);?></td>
</tr>
<?php } ?>


<?php if ($row[children]!='0'){ ?>
  <tr>
    <th colspan="2" scope="row">Children's allowance for missionaries working overseas</th>
    <td colspan="2" align="center">$<?php echo($row[child_allowance]); ?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Education allowanace for overseas missionaries with Children</th>
    <td colspan="2" align="center">$<?php echo($row[education_allowance]); ?></td>
  </tr>
<?php } ?>





  <tr>
    <th colspan="2" scope="row">Financial Packages </th>
    <td align="center">$<?php echo($row[Fin_Pack_1]+$row[top_up1]+$allow1);?></td>
    <td align="center">$<?php echo($row[Fin_Pack_2]+$row[top_up2]+$allow2);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Joint Financial Package</th>
    <td colspan="2" align="center">$<?php echo(($row[Fin_Pack_1]+$row[Fin_Pack_2]+$allow1+$allow2+$row[top_up1]+$row[top_up2]));?></td>
  </tr>

  <tr>
    <td colspan="4"><hr></td>
  </tr>

  <tr>
    <th colspan="2" scope="row">Employer Super </th>
    <td align="center">$<?php echo($row[Employer_Super_1]);?></td>
    <td align="center">$<?php echo($row[Employer_Super_2]);?></td>
  </tr>
<tr>
<th colspan="2" scope="row">Total Super </th>
<td align="center">$<?php echo($row[Employer_Super_1]+$row[Super_Pretax_1]+$row[Additional_Super_1]+$row[top_up1]); ?></td>
<td align="center">$<?php echo($row[Employer_Super_2]+$row[Super_Pretax_2]+$row[Additional_Super_2]+$row[top_up2]); ?></td>
</tr>
<tr>
    <th colspan="2" scope="row">Reportable Employer Super Contribution</td>
    <td align="center">$<?php 
if ($row[position1]=="Overseas"){
$os_fp_var1=$row[LAFHA_1];
} else {
$os_fp_var1=$row[MFB_1];
}
$os_fp1=$row[Tax_Inc_1] + $row[Super_Pretax_1] + $row[Additional_Life_Cover_1] + $os_fp_var1;
$resc1=round(($row[Employer_Super_1]+$row[Super_Pretax_1]+$row[Additional_Life_Cover_1]+$row[top_up1]-($os_fp1/10)),0);


if ($resc1 > "0"){
echo ($resc1);
} else {
echo "0";
}
?></td>
    <td align="center">$<?php 

if ($row[position1]=="Overseas"){
$os_fp_var2=$row[LAFHA_2];
} else {
$os_fp_var2=$row[MFB_2];
}

$os_fp2=$row[Tax_Inc_2] + $row[Super_Pretax_2] + $row[Additional_Life_Cover_2] + $os_fp_var2;
$resc2=round(($row[Employer_Super_2]+$row[Super_Pretax_2]+$row[Additional_Life_Cover_2]+$row[top_up2]-($os_fp2/10)),0);
if ($resc2 > "0"){
echo ($resc2);
} else {
echo "0";
}
?></td>
  </tr>
  <tr>
    <th scope="row" colspan="2">Super Choice:</th>
    <td align="center"><?php echo($row[Super_FundChoice_1]);?></td>
    <td align="center"><?php echo($row[Super_FundChoice_2]);?></td>
  </tr>

<?php
if ($row[Super_FundChoice_1]=='IOOF' || $row[Super_FundChoice_2] =='IOOF'){
?>
  <tr>
    <th scope="row" colspan="2">Additional Income Protection Premium paid from:</th>
    <td align="center"><?php echo($row[Premium_1]);?></td>
    <td align="center"><?php echo($row[Premium_2]);?></td>
  </tr>

<?php
}
?>

  <?php
  if ($housing!='0'){
 if($row[position1]!="Overseas"){
  ?>
  <tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row"><?php echo($house_state); ?></th>
    <td colspan="2" align="center">$<?php echo($housing); ?> of which $<?php echo($AHA);?> is additional housing - <?php echo($row[Housing_Change]); ?></td>
  </tr>
   <tr>
    <th colspan="2" scope="row">Frequency of Housing Payments</th>
    <td align="center" colspan="2"><?php echo($row[Housing_Freq]);?></td>
  </tr>
    <?php }
}
 ?>


  <tr>
    <td colspan="4"><hr></td>
  </tr>

  <tr>
    <th colspan="2" scope="row">MMR</th>
    <td colspan="2" align="center">$<?php echo($row[MMR]);?></td>
    </tr>

<?php
if ($row[Transfer_1]!=''){
?>
<tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Contribution Transfers:</th>
    <td align="center"><?php echo($row[Transfer_1]);?></td>
    <td align="center"><table width="100%"><td width="40%">$<?php echo($row[Transfer_1_amount]);?> </td><td>- <?php echo($row[Transfer_1_change]);?></td></table></td>
  </tr>
  <?php
}
  $i=2;
if($row['Transfer_'.$i]==''){
$i=11;
}
  while ($i<'11'){
if($row['Transfer_'.$i]!=''){
  ?>
  <tr>
    <th colspan="2" scope="row">&nbsp;</th>
    <td align="center"><?php echo($row['Transfer_'.$i]);?></td>
    <td align="center"><table width="100%"><td width="40%">$<?php echo($row['Transfer_'.$i.'_amount']);?></td><td>- <?php echo($row['Transfer_'.$i.'_change']);?></td></table></td>
  </tr>
  <?php

}
  $i++;
  }
  ?><tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Total Contribution Transfers</th>
    <td align="center" colspan="2">$<?php echo($row[Transfer_Totals]);?></td>
  </tr>


<?php if($row[position1]!="Overseas"){ ?>
<tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Worker's Compensation</th>
    <td align="center" colspan="2">$<?php echo($row[WorkerCompensation]);?></td>
  </tr>

<?php } ?>
  <?php
  if ($row[MPD]=='Yes'){
  ?>
  <tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th scope="row" colspan="2">Buffer Required (extra per month)</th>
    <td align="center" colspan="2">$<?php echo($row[SupportBufferNeeded]);?></td>
  </tr>
  <?php
  }
  ?>
<tr>
    <td colspan="4"><hr></td>
  </tr>
		<?php if($row[position1]!="Overseas"){ ?>
		  <tr>
		    <th colspan="2" scope="row">CCCA Levy</th>
		    <td align="center" colspan="2">$<?php echo($row[CCCA_levy]);?></td>
		  </tr>
		<?php } else { 
		$im_levy=round(($row[CCCA_levy]/2),0);
		$ccca_levy=$row[CCCA_levy]-$im_levy;
		?>
		  <tr>
		    <th colspan="2" scope="row"><?php echo($row[IM]);?> International Levy</th>
		    <td align="center" colspan="2">$<?php echo($im_levy);?></td>
		  </tr>
		  <tr>
		    <th colspan="2" scope="row">CCCA Levy</th>
		    <td align="center" colspan="2">$<?php echo($ccca_levy);?></td>
		  </tr>
		<?php } ?>
<tr>
    <td colspan="4"><hr></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Total Monthly Needs</th>
    <td align="center" colspan="2">$<?php echo($row[TMN]);?></td>
  </tr>
  <tr>
    <td colspan="4"><hr></td>
  </tr>
<?php if($row[position1]=="Overseas"){ ?>
<tr>
<th colspan="2" scope="row">Gross Stipend while on Home Assignment</th>
<td align="center">$<?php echo(($row[Home_TI1]-$row[Adtnl_Tax_1]-$row[Additional_Super_1]));?></td>
<td align="center">$<?php echo(($row[Home_TI2]-$row[Adtnl_Tax_2]-$row[Additional_Super_2]));?></td>
</tr>
  <tr>
    <th colspan="2" scope="row">Additional Tax while on Home Assignment</th>
    <td align="center">$<?php echo($row[Adtnl_Tax_1]);?></td>
    <td align="center">$<?php echo($row[Adtnl_Tax_2]);?></td>
  </tr>
<?php
if ($row[tax_stat]=="Resident"){
?>
  <tr>
    <th colspan="2" scope="row">Post-tax Super while on Home Assignment</th>
    <td align="center">$<?php echo($row[Additional_Super_1]);?></td>
    <td align="center">$<?php echo($row[Additional_Super_2]);?></td>
  </tr>
<?php
}
?>
  <tr>
    <th colspan="2" scope="row">Pre-tax Super while on Home Assignment</th>
    <td align="center">$<?php echo($row[Super_Pretax_1]);?></td>
    <td align="center">$<?php echo($row[Super_Pretax_2]);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Additional Life Cover (per month) while on Home Assignment</th>
    <td align="center">$<?php echo($row[Additional_Life_Cover_1]);?></td>
    <td align="center">$<?php echo($row[Additional_Life_Cover_2]);?></td>
  </tr>
  <tr>
    <th colspan="2" scope="row">Employer Super while on Home Assignment</th>
    <td align="center">$<?php 
if ($row[Home_TI1] >= "450"){
$emp_sup_ho_1=(round((0.09)*($row[Home_TI1]),0));
echo($emp_sup_ho_1);
} else {
echo "0";
}
?></td>
    <td align="center">$<?php 
if ($row[Home_TI2] >= "450"){
$emp_sup_ho_2=(round((0.09)*($row[Home_TI2]),0));
echo($emp_sup_ho_2);
} else {
echo "0";
} 
?></td>
  </tr>
<tr>
<?php if ($row[top_up1]!="0" || $row[top_up2]!="0"){?>
<th colspan="2" scope="row">Super Top Up while on Home Assignment</th>
<td align="center">$<?php
echo($row[top_up1]);
?></td>
    <td align="center">$<?php 
echo($row[top_up2]);
?></td></tr>
<?php } ?>
<tr>
    <th colspan="2" scope="row">Reportable Employer Super Contribution while on Home Assignment</td>
    <td align="center">$<?php 
$fin_home1=$row[Home_TI1] + $row[Super_Pretax_1] + $row[Additional_Life_Cover_1] + $row[MFB_1];

$resc1=round(($emp_sup_ho_1+$row[Super_Pretax_1]+$row[Additional_Life_Cover_1]+$row[top_up1]-(($fin_home1)/10)),0);


if ($resc1 > "0"){
echo ($resc1);
} else {
echo "0";
}
?></td>
    <td align="center">$<?php 

$fin_home2=$row[Home_TI2] + $row[Super_Pretax_2] + $row[Additional_Life_Cover_2] + $row[MFB_2];

$resc2=round(($emp_sup_ho_2+$row[Super_Pretax_2]+$row[Additional_Life_Cover_2]+$row[top_up2]-(($fin_home2)/10)),0);


if ($resc2 > "0"){
echo ($resc2);
} else {
echo "0";
}
?></td>
  </tr>
<tr>
<th colspan="2" scope="row">MFB's that can be claimed while on Home Assignment</th>
    <td align="center">$<?php echo($row[MFB_1]); ?></td>
    <td align="center">$<?php echo($row[MFB_2]); ?></td>
</tr>
<tr>
    <th colspan="2" scope="row"><?php echo($house_state); ?></th>
    <td colspan="2" align="center">$<?php echo($housing); ?></td>
</tr>



<tr>
    <td colspan="4"><hr></td>
  </tr>
<?php } ?>
 </table>
<p align="right">Revision Date: <?php
$sql= mysql_query("SELECT * FROM ccca_forms where id='1'");
$row=mysql_fetch_array($sql);
list($year, $month, $day) = split('-', $row[rev_date]);
echo($day."/".$month."/".$year);
?></p>
</body>
</html>

