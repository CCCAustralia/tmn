<?php
include_once("mysqldriver.php");
include_once("logger.php");
include_once("./calc/calc_tax.php");
include_once("./calc/calc_mfbmax.php");
include_once("./calc/calc_employersuper.php");
include_once("./calc/calc_additionalhousing.php");
require_once("../lib/FirePHPCore/fb.php");

class finproc {
	//financial values
	private $STIPEND_MIN = 100;
	private $MIN_ADD_SUPER_RATE = 0.09;
	
	private $financial_data;
	private $DEBUG;
	private $connection;
	private $logger;
	
	public function __construct($findat, $dbug) {
		$this->financial_data = $findat;
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
		$this->logger = new logger("logs/financial.log");
		$this->logger->setDebug($this->DEBUG);
		if($this->DEBUG) fb("DEBUGGING MODE");
	}
	
	
	public function proc() {
	
		//if there is a spouse get the guid
		if ($this->financial_data['spouse']){
			$sql = mysql_query("SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$this->financial_data['guid']."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				$this->financial_data['spouse'] = $row['SPOUSE_GUID'];
			}
		}
		
		
		//Housing
		if (isset($this->financial_data['HOUSING'])){
			if (!isset($this->financial_data['HOUSING_FREQUENCY'])) $this->financial_data['HOUSING_FREQUENCY'] = 0;
			//convert fornightly housing to monthly housing
			if ($this->financial_data['HOUSING_FREQUENCY'] == 1)
				$monthly_housing = $this->financial_data['HOUSING'] * 26 / 12;
			else
				$monthly_housing = $this->financial_data['HOUSING'];
			//calc additional housing
			$this->financial_data['ADDITIONAL_HOUSING'] = calculateAdditionalHousing($this->financial_data['HOUSING'], $this->financial_data['HOUSING_FREQUENCY'], $this->financial_data['spouse']);
		}
		
		
		//Taxable Income Panel
		if (isset($this->financial_data['STIPEND'])){
			
			//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
			if (isset($this->financial_data['HOUSING']) && isset($this->financial_data['MAX_MFB']) && isset($this->financial_data['ADDITIONAL_HOUSING']))
			{
				if (isset($this->financial_data['S_MAX_MFB']))
					$this->financial_data['HOUSING_STIPEND'] = max(0, $monthly_housing - ($this->financial_data['MAX_MFB'] + $this->financial_data['S_MAX_MFB']) - $this->financial_data['ADDITIONAL_HOUSING']);
				else
					$this->financial_data['HOUSING_STIPEND'] = max(0, $monthly_housing - ($this->financial_data['MAX_MFB']) - $this->financial_data['ADDITIONAL_HOUSING']);
			}
				
			//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
			$this->financial_data['NET_STIPEND'] = $this->financial_data['STIPEND'] + $this->financial_data['HOUSING_STIPEND'];
			
			//check min stipend
			if ($this->financial_data['NET_STIPEND'] < $this->STIPEND_MIN){
				if($this->financial_data['HOUSING_STIPEND'] > 0)
					$err .= "\"NET_STIPEND\":\"Net Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
				else
					$err .= "\"STIPEND\":\"Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
			}
			
			$annum = ($this->financial_data['NET_STIPEND'] * 12) + ($this->financial_data['POST_TAX_SUPER'] * 12) + ($this->financial_data['ADDITIONAL_TAX'] * 12);	//calculate yearly figure
			
			$this->financial_data['TAXABLE_INCOME'] = calculateTaxableIncome($annum);
			$this->financial_data['TAX'] = calculateTax($this->financial_data['TAXABLE_INCOME'], 'resident');
			$this->financial_data['EMPLOYER_SUPER'] = calculateEmployerSuper($this->financial_data['TAXABLE_INCOME']);
			
			$this->financial_data['TAXABLE_INCOME'] = round($this->financial_data['TAXABLE_INCOME'] / 12);
			$this->financial_data['TAX'] = round($this->financial_data['TAX'] / 12);
		    $this->financial_data['EMPLOYER_SUPER'] = round($this->financial_data['EMPLOYER_SUPER'] / 12);
		}
		
		//Maximum MFB & Pre-tax Super
		if (isset($this->financial_data['TAXABLE_INCOME'])) {
		
			//enumerate mfb rate
			switch ($this->financial_data['MFB_RATE']) {
				case 0:
				//Zero MFBs
					$mfbrate = 0;
					break;
				case 1:
				//Half MFBs
					$mfbrate = 0.5;
					break;
				case 2:
				//Full MFBs
					$mfbrate = 1;
					break;
			}
		
			//Pre Tax Super (if its not set then set it to the min)
			if ($this->financial_data['pre_tax_super_mode'] == 'auto'){
				$this->financial_data['PRE_TAX_SUPER'] = round($this->financial_data['TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
			} else {
				$min_pre_tax_super = round($this->financial_data['TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
				if (!isset($this->financial_data['PRE_TAX_SUPER']) || $this->financial_data['PRE_TAX_SUPER'] < $min_pre_tax_super){
					$this->financial_data['PRE_TAX_SUPER'] = $min_pre_tax_super;
				}
			}
			
			//Fetch the user's days per week
			$sql = mysql_query("SELECT DAYS_PER_WEEK, FT_PT_OS FROM User_Profiles WHERE guid='".$this->financial_data['guid']."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				if ($row['FT_PT_OS'] == 0){
					$this->financial_data['DAYS_PER_WEEK'] = 4;
				} else {
					$this->financial_data['DAYS_PER_WEEK'] = $row['DAYS_PER_WEEK'];
				}
			}
			
			$this->financial_data['MAX_MFB'] = round(calculateMaxMFB($this->financial_data['TAXABLE_INCOME'], $mfbrate, $this->financial_data['DAYS_PER_WEEK'] + 1)); //+1 because days per week is stored as an index not a number
		}
		
		
		//Spouse Taxable Income Panel
		if (isset($this->financial_data['S_STIPEND'])){
					//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
			if (isset($this->financial_data['HOUSING']) && isset($this->financial_data['MAX_MFB']) && isset($this->financial_data['ADDITIONAL_HOUSING']))
			{
				if (isset($this->financial_data['S_MAX_MFB']))
					$this->financial_data['S_HOUSING_STIPEND'] = 0;
				else
					$this->financial_data['S_HOUSING_STIPEND'] = 0;
			}
				
			//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
			$this->financial_data['S_NET_STIPEND'] = $this->financial_data['S_STIPEND'] + $this->financial_data['S_HOUSING_STIPEND'];
			
			//check min stipend
			if ($this->financial_data['S_NET_STIPEND'] < $this->STIPEND_MIN){
				if($this->financial_data['S_HOUSING_STIPEND'] > 0)
					$err .= "\"S_NET_STIPEND\":\"Net Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
				else
					$err .= "\"S_STIPEND\":\"Stipend is too low: must be at least $".$this->STIPEND_MIN.".\", ";
			}
			
			$s_annum = ($this->financial_data['S_NET_STIPEND'] * 12) + ($this->financial_data['S_POST_TAX_SUPER'] * 12) + ($this->financial_data['S_ADDITIONAL_TAX'] * 12);	//calculate yearly figure
			
			$this->financial_data['S_TAXABLE_INCOME'] = calculateTaxableIncome($s_annum);
			$this->financial_data['S_TAX'] = calculateTax($this->financial_data['S_TAXABLE_INCOME'], 'resident');
			$this->financial_data['S_EMPLOYER_SUPER'] = calculateEmployerSuper($this->financial_data['S_TAXABLE_INCOME']);
			
			$this->financial_data['S_TAXABLE_INCOME'] = round($this->financial_data['S_TAXABLE_INCOME'] / 12);
			$this->financial_data['S_TAX'] = round($this->financial_data['S_TAX'] / 12);
		    $this->financial_data['S_EMPLOYER_SUPER'] = round($this->financial_data['S_EMPLOYER_SUPER'] / 12);
		}
		
		//Spouse Maximum MFB && Pre Tax Super
		if (isset($this->financial_data['S_TAXABLE_INCOME'])) {
		
			//enumerate mfb rate
			switch ($this->financial_data['S_MFB_RATE']) {
				case 0:
				//Zero MFBs
					$mfbrate = 0;
					break;
				case 1:
				//Half MFBs
					$mfbrate = 0.5;
					break;
				case 2:
				//Full MFBs
					$mfbrate = 1;
					break;
			}
			
			//Spouse Pre Tax Super (if its not set then set it to the min)
			if ($this->financial_data['s_pre_tax_super_mode'] == 'auto'){
				$this->financial_data['S_PRE_TAX_SUPER'] = round($this->financial_data['S_TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
			} else {
				$s_min_pre_tax_super = round($this->financial_data['S_TAXABLE_INCOME'] * $mfbrate * $this->MIN_ADD_SUPER_RATE);
				if (!isset($this->financial_data['S_PRE_TAX_SUPER']) || $this->financial_data['S_PRE_TAX_SUPER'] < $s_min_pre_tax_super){
					$this->financial_data['S_PRE_TAX_SUPER'] = $s_min_pre_tax_super;
				}
			}
			
			//Fetch the user's days per week
			$sql = mysql_query("SELECT DAYS_PER_WEEK FT_PT_OS FROM User_Profiles WHERE guid='".$this->financial_data['spouse']."'"); //needs to change ($this->financial_data['spouse'] doesn't hold spouse guid)
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				if ($row['FT_PT_OS'] == 0){
					$this->financial_data['S_DAYS_PER_WEEK'] = 4;
				} else {
					$this->financial_data['S_DAYS_PER_WEEK'] = $row['DAYS_PER_WEEK'];
				}
			}
			
			$this->financial_data['S_MAX_MFB'] = round(calculateMaxMFB($this->financial_data['S_TAXABLE_INCOME'], $mfbrate, $this->financial_data['S_DAYS_PER_WEEK'] + 1)); //+1 because days per week is stored as an index not a number
		}
		
		if ($this->DEBUG) fb($this->financial_data);
		
		if ($err == '') {
		
			$result = array('success'=>'true');
			$result['financial_data'] = $this->financial_data;
			return json_encode($result);
		}
		else {
			return '{"success": false, "errors":{'.trim($err,", ").'} }';	//Return with errors
		}
	}
}
?>