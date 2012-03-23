<?php
if (file_exists("mysqldriver.php")) {
	include_once("mysqldriver.php");
	include_once("logger.php");
	include_once("../lib/FirePHPCore/fb.php");
	include_once('../lib/cas/cas.php');		//include the CAS module
} elseif (file_exists("../mysqldriver.php")) {
	include_once("../mysqldriver.php");
	include_once("../logger.php");
	include_once("../../lib/FirePHPCore/fb.php");
	include_once('../../lib/cas/cas.php');		//include the CAS module
} else {
	include_once("php/mysqldriver.php");
	include_once("php/logger.php");
	include_once("lib/FirePHPCore/fb.php");
	include_once('lib/cas/cas.php');
}

define("FOR_USER", 0);
define("FOR_SPOUSE", 1);
define("HOUSING_FREQUENCY_MONTHLY", 0);
define("HOUSING_FREQUENCY_FORTNIGHTLY", 1);


//Authenticate the user in GCX with phpCAS
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

class FinancialProcessor {
	
	protected $constants;
	
	//personal details
	protected $guid;
	protected $spouse;
				
	protected $days_per_week = 0;
	protected $s_days_per_week = 0;
	
	protected $financial_data;
	protected $DEBUG;
	protected $connection;
	protected $logger;
	
	
	//__construct:			This is the constructor and will initalise the object when created
	//params:				$findat		- an associative array that contains the financial data
	//						$dbug		- (number 0,1) tells the object if it should use debug mode or not
	//returns				n/a
	public function __construct($findat, $dbug) {
		
		
		//tax values
		//formula and values grabbed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//Scale 7 (Where payee not eligible to receive leave loading and has claimed tax-free threshold)
		
		//////////      DONE      //////////
						
		$this->financial_data = $this->typeCastForArray($findat);
			//grab guid
		if (isset($_SESSION['phpCAS'])) {
			$xmlstr = str_replace("cas:", "", $_SESSION['phpCAS']['serviceResponse']);
			$xmlobject = new SimpleXmlElement($xmlstr);
			$this->guid = (string)$xmlobject->authenticationSuccess->attributes->ssoGuid;
		}
		$this->spouse = $this->financial_data['spouse'];
		$this->DEBUG = $dbug;
		$this->connection = new MySqlDriver();
		$this->logger = new logger("logs/financial.log");
		$this->logger->setDebug($this->DEBUG);
		if($this->DEBUG) fb("DEBUGGING MODE");		
		//choose the appropriate set of tax figures
		
		//////////  SET UP CONSTANTS  //////////
		include_once('classes/TmnConstants.php');
		$this->constants = getConstants(getVersionNumber(), $this->financial_data['OS_RESIDENT_FOR_TAX_PURPOSES']);
		
		if($this->DEBUG) fb($this->constants['x']);
	}
	
	
	//proc:				processes the financial data and returns the result as a json object
	//params:			n/a
	//returns			a string that describes a json object (will contain {"success": "true", "financial_data": ... } or {"success": "false", "err": ... }
	public function process() {
		
		$err	= array();
	
		//Spouse Guid
		$this->spouse = $this->getSpouseGuid();
	
		//OVERSEAS PROCESSING
		if ($this->financial_data['overseas'] && !$this->financial_data['home_assignment']) {
			//Stipend
			//calculate the extra stipend
			$overflow = $this->financial_data['STIPEND'] - $this->constants['OS_STIPEND_MAX'];
			//check if it is over the limit
			if ($overflow > 0) {
				//truncate the stipend
				$this->financial_data['STIPEND'] = $this->financial_data['STIPEND'] - $overflow;
				
				//add the overflow to LAFHA
				$this->financial_data['OS_LAFHA'] += $overflow;
				
				//return warnings explaining the changes
				$warnings['STIPEND'] = "\"Your stipend was over the maximum of $".$this->constants['OS_STIPEND_MAX'].".<br />The extra amount ($".$overflow.") was added to your LAFHA to compensate.<br />Please review these figures before submitting.\"";
				$warnings['OS_LAFHA']= "\"Your stipend was over the maximum of $".$this->constants['OS_STIPEND_MAX'].".<br />The extra amount ($".$overflow.") was added to your LAFHA to compensate.<br />Please review these figures before submitting.\"";
			} else {
				if (!isset($this->financial_data['OS_LAFHA']))
					$this->financial_data['OS_LAFHA'] = 0;
			}
			
			//spouse
			if ($this->spouse) {
				//calculate the extra stipend
				$s_overflow = $this->financial_data['S_STIPEND'] - $this->constants['OS_STIPEND_MAX'];
				//check if it is over the limit
				if ($s_overflow > 0) {
					//truncate the stipend
					$this->financial_data['S_STIPEND'] = $this->financial_data['S_STIPEND'] - $s_overflow;
					
					//add the overflow to LAFHA
					$this->financial_data['S_OS_LAFHA'] += $s_overflow;
					
					//return warnings explaining the changes
					$warnings['S_STIPEND']	= "\"Your spouse's stipend was over the maximum of $".$this->constants['OS_STIPEND_MAX'].".<br />The extra amount ($".$s_overflow.") was added to their LAFHA to compensate.<br />Please review these figures before submitting.\"";
					$warnings['S_OS_LAFHA']	= "\"Your spouse's stipend was over the maximum of $".$this->constants['OS_STIPEND_MAX'].".<br />The extra amount ($".$s_overflow.") was added to their LAFHA to compensate.<br />Please review these figures before submitting.\"";
				} else {
					if (!isset($this->financial_data['S_OS_LAFHA']))
						$this->financial_data['S_OS_LAFHA'] = 0;
				}
			}

			if($this->DEBUG) fb($this->financial_data['OS_LAFHA']);
			
			//LAFHA
			////The LAFHA may not be more than zero if the stipend is less than the maximum
			if ($overflow <= 0 && $this->financial_data['OS_LAFHA'] != 0) {
				$difference = $this->constants['OS_STIPEND_MAX'] - $this->financial_data['STIPEND'];
				if($this->DEBUG) fb($difference);
				if ($this->financial_data['OS_LAFHA'] > $difference) {
					$this->financial_data['STIPEND'] += $difference;
					$this->financial_data['OS_LAFHA'] = $this->financial_data['OS_LAFHA'] - $difference;
				} else {
					$this->financial_data['STIPEND'] += $this->financial_data['OS_LAFHA'];
					$this->financial_data['OS_LAFHA'] = 0;
				}	
			}
			//spouse
			if ($this->spouse) {
				if ($s_overflow <= 0 && $this->financial_data['S_OS_LAFHA'] != 0) {
					$s_difference = $this->constants['OS_STIPEND_MAX'] - $this->financial_data['S_STIPEND'];
					if ($this->financial_data['S_OS_LAFHA'] > $s_difference) {
						$this->financial_data['S_STIPEND'] += $s_difference;
						$this->financial_data['S_OS_LAFHA'] = $this->financial_data['S_OS_LAFHA'] - $s_difference;
					} else {
						$this->financial_data['S_STIPEND'] += $this->financial_data['S_OS_LAFHA'];
						$this->financial_data['S_OS_LAFHA'] = 0;
					}	
				}
			}
		}
		
		//Housing
		$this->financial_data['ADDITIONAL_HOUSING'] = $this->getAdditionalHousing();
		
		//Taxable Income Panel
		
		
		
		//Taxable Income Panel
		if (isset($this->financial_data['STIPEND'])){
			
			//Housing Stipend while debug will return 0
			$this->financial_data['HOUSING_STIPEND'] = $this->getHousingStipend();
		if ($this->DEBUG) fb($this->financial_data);
			//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
			$this->financial_data['NET_STIPEND'] = $this->financial_data['STIPEND'] + $this->financial_data['HOUSING_STIPEND'];
			
			//check min stipend
			$msg	= $this->validateStipend($this->financial_data['NET_STIPEND'], $this->financial_data['HOUSING_STIPEND']);
			if (is_null($msg)) {
				$err['NET_STIPEND'] = $msg;
			}
			
			$annum = $this->financial_data['NET_STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];	//calculate yearly figure
			
			$this->financial_data['TAXABLE_INCOME'] = $this->calculateTaxableIncome($annum, $this->x, $this->a, $this->b);
			$this->financial_data['TAX'] = $this->calculateTax($this->financial_data['TAXABLE_INCOME'], $this->x, $this->a, $this->b);
			$this->financial_data['EMPLOYER_SUPER'] = $this->calculateEmployerSuper($this->financial_data['TAXABLE_INCOME'], $this->constants['MIN_SUPER_RATE']);
		}
		
		//Maximum MFB & Pre-tax Super
		if (isset($this->financial_data['TAXABLE_INCOME'])) {
		
			//mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
		
			//Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['PRE_TAX_SUPER'] = $this->getPreTaxSuper($mfbrate, FOR_USER);
			
			//Fetch Days Per Week
			$this->financial_data['DAYS_PER_WEEK'] = $this->getDaysPerWeek(FOR_USER);
			
			//calc max mfbs
			$this->financial_data['MAX_MFB'] = round($this->calculateMaxMFB($this->financial_data['TAXABLE_INCOME'], $mfbrate, $this->financial_data['DAYS_PER_WEEK']));
			
			//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
			$this->financial_data['CLAIMABLE_MFB'] = $this->getClaimableMfb(FOR_USER);
		}
		
		
		//Spouse Taxable Income Panel
		if ($this->spouse) {
			if (isset($this->financial_data['S_STIPEND'])){
						//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
						$this->financial_data['S_HOUSING_STIPEND'] = 0;
					
				//calc net stipend (stipend (money in your account) + housing stipend (extra stipend needed to cover housing amount)
				$this->financial_data['S_NET_STIPEND'] = $this->financial_data['S_STIPEND'] + $this->financial_data['S_HOUSING_STIPEND'];
				
				//check min stipend
				$msg	= $this->validateStipend($this->financial_data['S_NET_STIPEND'], $this->financial_data['S_HOUSING_STIPEND']);
				if (is_null($msg)) {
					$err['S_NET_STIPEND'] = $msg;
				}
				
				$s_annum = $this->financial_data['S_NET_STIPEND'] + $this->financial_data['S_POST_TAX_SUPER'] + $this->financial_data['S_ADDITIONAL_TAX'];	//calculate yearly figure
				
				$this->financial_data['S_TAXABLE_INCOME'] = $this->calculateTaxableIncome($s_annum, $this->x, $this->a, $this->b);
				$this->financial_data['S_TAX'] = $this->calculateTax($this->financial_data['S_TAXABLE_INCOME'], $this->x, $this->a, $this->b);
				$this->financial_data['S_EMPLOYER_SUPER'] = $this->calculateEmployerSuper($this->financial_data['S_TAXABLE_INCOME'], $this->constants['MIN_SUPER_RATE']);
			}
			
			//Spouse Maximum MFB && Pre Tax Super
			if (isset($this->financial_data['S_TAXABLE_INCOME'])) {
			
				//enumerate mfb rate
				$mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
				
				//Spouse Pre Tax Super (if its not set then set it to the min)
				$this->financial_data['S_PRE_TAX_SUPER']	= $this->getPreTaxSuper($mfbrate, FOR_SPOUSE);
				
				//Fetch the user's days per week
				$this->financial_data['S_DAYS_PER_WEEK']	= $this->getDaysPerWeek(FOR_SPOUSE);
				
				$this->financial_data['S_MAX_MFB']			= round($this->calculateMaxMFB($this->financial_data['S_TAXABLE_INCOME'], $mfbrate, $this->financial_data['S_DAYS_PER_WEEK']));
				
				//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
				$this->financial_data['S_CLAIMABLE_MFB']	= $this->getClaimableMfb(FOR_SPOUSE);
			}
		}
		
		
	
		if ($this->DEBUG) fb($this->financial_data);
		
		if (count($err) == 0) {
		
			$result = array('success'=> true );
			$result['financial_data'] = $this->financial_data;
			$result['warnings'] = $warnings;
			
			if($this->DEBUG) fb($result);
			
			return json_encode($result);
			
		} else {
			
			$result = array('success' => false);
			$result['errors'] = $err;
			
			if($this->DEBUG) fb($result);
			
			return json_encode($result);	//Return with errors
			
		}
		
	}
	
	/**
	 * Runs over array changing values from strings to there orginal type. (works for number to int and bool to bool)
	 * @param array $assoc_array
	 * @return array
	 */
	private function typeCastForArray($assoc_array) {
		foreach ($assoc_array as $key => $value) {
			if (is_numeric($value)) {
				$assoc_array[$key] = (int)$value;
			} elseif ($value == "true") {
				$assoc_array[$key] = true;
			} elseif ($value == "false") {
				$assoc_array[$key] = false;
			} else {
				$assoc_array[$key] = $value;
			}
		}
		
		return $assoc_array;
	}
	
	public function calculateTaxableIncomeComponentsForAussie($stipend, $post_tax_super, $additional_tax, $monthly_housing, $additional_housing_allowance, $mfb_rate, $days_per_week, $future_investment_mode, $constants) {
		
		$netIncome			= $stipend + $post_tax_super + $additional_tax;
		$oldTaxableIncome	= $this->calculateTaxableIncome($netIncome, $constants);
		$housingStipend		= 0;
		$futureInvestment	= 0;
		$change				= PHP_INT_MAX;
		$accuracy			= 1;
		$iterations			= 0;
		$maxIterations		= 100;
		$maxMfbs			= $this->calculateMaxMFB($oldTaxableIncome, $mfb_rate, $days_per_week);
		$housingLessAdditionalHousingAllowance		= $this->getMonthlyHousing() - $this->getAdditionalHousing();
		
		//while (housing stipend needs to be calculated or a taxable future investment needs to be calculated) and (we are above acceptable accuracy or less than an acceptable number of iterations)
		while ( ( $housingLessAdditionalHousingAllowance > $maxMfbs || $future_investment_mode != 0) && ($change >= $accuracy && $iterations < $maxIterations) ) {
			
			//if a taxable future investment needs to be calculated
			if ($future_investment_mode != 0) {
				
				$futureInvestment	= $oldTaxableIncome * $constants['MIN_SUPER_RATE'];
				$newTaxableIncome	+= $this->calculateTaxableIncome($netIncome + $futureInvestment, $constants);
				
				//recalc max mfb with new Taxable Income
				$maxMfbs			= $this->calculateMaxMFB($newTaxableIncome, $mfb_rate, $days_per_week);
				
			} else {
				
				$newTaxableIncome	= $oldTaxableIncome;
				$futureInvestment	= 0;
				
			}
			
			//housing stipend needs to be calculated
			if ($housingLessAdditionalHousingAllowance > $maxMfbs) {
				
				$housingStipend		= $housingLessAdditionalHousingAllowance - $maxMfbs;
				$newTaxableIncome	= $this->calculateTaxableIncome($netIncome + $futureInvestment + $housingStipend, $constants);
				
				//recalc max mfb with new Taxable Income
				$maxMfbs			= $this->calculateMaxMFB($newTaxableIncome, $mfb_rate, $days_per_week);
				
			}
			
			//update change and iteration variables
			$change				= abs($newTaxableIncome - $oldTaxableIncome);
			$oldTaxableIncome	= $newTaxableIncome;
			$iterations++;
			
		}
		
		return array(
			"taxable_income"	=> $oldTaxableIncome,
			"future_investment"	=> $futureInvestment,
			"housing_stipend"	=> $housingStipend
		);
		
	}
	
	//calculateEmployerSuper:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:					$taxable_income - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//returns					employer super (a whole number >= 0)
	public function calculateEmployerSuper($taxable_income, $constants){
		
		return	round($taxable_income * $constants['MIN_SUPER_RATE']);
		
	}
	
	
	//calculateMaxMFB:			Calculates and returns the max mfbs a person can take
	//params:					$taxable_income - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//							$mfbrate - (a decimal number 0 <= $mfbrate <= 1) the rate of mfbs they are entitled to (atm they are 0% 25% 50% of your taxable income)
	//							$daysperweek - (a whole number 1,2,3,4,5)
	//returns					maxmfb (a whole number >= 0)
	public function calculateMaxMFB($taxable_income, $mfbrate, $daysperweek) {
		
		$maxmfb = $taxable_income;
		
		$maxmfb = $maxmfb * ($mfbrate);
		
		//$maxmfb = $maxmfb * ($daysperweek / 5);
		
		return $maxmfb;
	}
	
	
	//calculateMaxWage:					Takes an index of the weekly tax ranges and returns the range for a wage
	//params:							$index - (an int >= 0) the index of a range in the $this->x variable
	//returns							weekly max wage (a whole number >= 0)
	public function calculateMaxWage($index, $constants) {
		//formula and values grabed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//the max taxable income - the tax of max taxable income gives us the max wage for that tax bracket
		return $constants['x'][$index] - round($constants['a'][$index] * (floor($constants['x'][$index]) + 0.99) - $constants['b'][$index]);
	}
	
	
	//calculateTaxableIncome:			Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly taxable income (a number >= 0)
	public function calculateTaxableIncome($wage, $constants){
		return $wage + $this->calculateTaxFromWage($wage, $constants);
	}
	
	
	//calculateTaxFromWage:				Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly tax (a whole number >= 0)
	public function calculateTaxFromWage($wage, $constants) {
		//formula and values derived from:
		//Statement of formulas for calculating amounts to be withheld
		if($this->DEBUG) fb("wage: ".$wage);
		//convert from months to weeks
		$wage = floor(floor($wage) * 12 / 52);
		
		for ( $rangeCount = 0; $rangeCount < count($constants['x']); $rangeCount++ ) {
			if ($wage < $this->calculateMaxWage($rangeCount, $constants))
				break;
		}
		if($this->DEBUG) fb("rangecount: ".$rangeCount);
		return round(ceil(($constants['a'][$rangeCount] * ($wage) - $constants['b'][$rangeCount]) / (1 - $constants['a'][$rangeCount])) * 52 / 12);
	}
	
	
	//calculateTax:						Takes a monthly taxable income and returns the tax (formula and values grabed from: Statement of formulas for calculating amounts to be withheld. On the ATO website)
	//params:							$taxable_income - (a whole number > 0) the weekly taxable income
	//returns							monthly tax (a whole number >= 0)
	public function calculateTax($taxable_income, $constants) {
		//formula and values grabbed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//ATO rounding for monthly to weekly convertion (if $taxable_income ends with 33 cents then add one cent)
		if ( ( $taxable_income - floor($taxable_income) ) == 0.33 ) $taxable_income += 0.01;
		
		//convert from monthly to weekly
		$taxable_income = $taxable_income * 3 / 13; //same as $taxable_income = $taxable_income * 12 / 52
		
		//ATO rounding for weekly Tax calculation (ignore cents and add 0.99)
		$taxable_income = floor($taxable_income) + 0.99;
		
		//find which weekly tax bracket $taxable_income falls in
		for( $rangeCount = 0; $rangeCount < count($constants['x']); $rangeCount++ ){
			if ($taxable_income < $constants['x'][$rangeCount])
				break;
		}
		//calculate tax
		$tax = round($constants['a'][$rangeCount] * $taxable_income - $constants['b'][$rangeCount]);
		//convert back to monthly before returning
		return round($tax * 13 / 3); //same as $tax * 52 / 12
	}
	
	
	//getAdditionalHousing:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:				n/a
	//returns				additional_housing (a number >= 0)
	public function calculateAdditionalHousingAllowance($monthly_housing){
		
		//get max cut off
		$max_housing_mfb = $this->getMaxHousingMfb();
			
		//calc additional housing
		return round(max( 0, ($monthly_housing - $max_housing_mfb) ));
			
	}
	
	//getClaimableMfb:	Calculates a persons claimable mfbs (the mfbs that are left after your housing has been taken out)
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your pretax super or the spouses, 1, pretax super
	//returns			claimable mfb (a number >= 0)
	public function calculateClaimableMfb($user_or_spouse, $monthly_housing, $user_max_mfb, $spouse_max_mfb = 0) {
		
		$max_housing_mfb = $this->getMaxHousingMfb();
		
		//for a single
		if ($this->spouse) {
			
			if ($user_or_spouse == FOR_USER) {
				
				//calcs single claimable mfb otherwise
				return max(0, $user_max_mfb - min($monthly_housing, $max_housing_mfb));
				
			} else {
				
				//returns 0 for spouse given the user is single
				return 0;
				
			}
			
		//for a married user
		} else {
			
			if ($user_or_spouse == FOR_USER) {
				
				//calcs single claimable mfb for the primary user
				return max(0, $user_max_mfb - min($monthly_housing, $max_housing_mfb));
				
			} else {
				
				//calcs spouse claimable mfb (take user mfb off housing first and if there is housing still to be coverd take it from spouse mfb)
				return max(0, $spouse_max_mfb - max(0, min($monthly_housing, $max_housing_mfb) - $user_max_mfb));
				
			}
			
		}
			
	}
	
	//getMonthlyHousing:	Uses the housing frequency to return the housing amount as a monthly figure
	//params:				n/a
	//returns				monthly_housing (a number >= 0)
	public function calculateMonthlyHousing($housing, $housing_frequency) {
		
		//convert fornightly housing to monthly housing
		if ($housing_frequency == HOUSING_FREQUENCY_FORTNIGHTLY) {
			
			return round($housing * 26 / 12);
			
		} else {
			
			return $housing;
			
		}
		
	}
	
	
	//getPreTaxSuper:	Calculates a persons pretax super and returns it
	//params:			$mfbrate		- (a number 0-1) give it the rate to multiply it by
	//					$me_or_spouse	- (number 0 or 1) tells function wether you want your pretax super or the spouses pretax super
	//returns			pretax super (a number > 0)
	public function calculatePreTaxSuper($taxable_income, $mfbrate, $constants, $pre_tax_super_mode, $pre_tax_super = 0){
	
		//Pre Tax Super (if its not set then set it to the min)
		if ($pre_tax_super_mode == 'auto') {
			
			return round($taxable_income * $mfbrate * $constants['MIN_ADD_SUPER_RATE']);
			
		} else {
			
			$min_pre_tax_super = round($taxable_income * $mfbrate * $constants['MIN_ADD_SUPER_RATE']);
			
			//if the manual value is bigger than the minimum amount then return the manual amount
			if ($pre_tax_super > $min_pre_tax_super) {
				
				return $pre_tax_super;
				
			//if the manual amount isn't high enough then return the minimum amount
			} else {
				
				return $min_pre_tax_super;
				
			}
			
		}
		
	}
	
	//getMaxHousingMfb:		It will return the max housing mfb based on if there is a spouse or not
	//						(this is the limit of how much of your mfbs will go toward housing)
	//params:				n/a
	//returns				max housing mfb (a number > 0)
	public function calculateMaxHousingMfb($has_spouse, $constants){
		//update 2011 - fetch from database as a constant. was 960/1600c
		return $has_spouse ? $constants['MAX_HOUSING_MFB_COUPLES'] : $constants['MAX_HOUSING_MFB'];
	}
	
	//getMfbRate:		Takes an MFB_RATE index and returns the actual rate
	//params:			$MFB_RATE		- (number 0,1,2) give it the rate index
	//returns			mfbrate (a number 0-1)
	public function calculateFractionFromMfbRate($MFB_RATE){
		//enumerate mfb rate
		switch ($MFB_RATE) {
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
			default:
			//Full MFBs
				$mfbrate = 1;
				break;
		}
		return $mfbrate;
	}
	
	//validateStipend:		Checks if stipend is valid, if not it returns an error message
	//params:				$me_or_spouse	- (number 0 or 1) tells function wether you want your values or your spouses values
	//returns				error message (a string, empty if valid)
	public function validateStipend($stipend, $housingStipend){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		//check min stipend
		if ($stipend < $this->constants['STIPEND_MIN']){
			if($housingStipend > 0)
				return "Net Stipend is too low: must be at least $" . $this->constants['STIPEND_MIN'] . ".";
			else
				return "Stipend is too low: must be at least $" . $this->constants['STIPEND_MIN'] . ".";
		}
		
		return null;
	}
	
	/*
	//getAdditionalHousing:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:				n/a
	//returns				additional_housing (a number >= 0)
	public function getAdditionalHousing(){
		if (isset($this->financial_data['HOUSING'])){
		
			//get max cut off
			$maxhousingmfb = $this->getMaxHousingMfb();
			
			//set housing freq to the default if not set
			if (!isset($this->financial_data['HOUSING_FREQUENCY'])) $this->financial_data['HOUSING_FREQUENCY'] = 0;
			
			//make sure housing is monthly
			$monthly_housing = $this->getMonthlyHousing();
			
			//get days per week ratio
			//TODO: apply $days_per_week_ratio to the calculation
			if ($this->spouse)
				$days_per_week_ratio = ($this->getDaysPerWeek(FOR_USER) + $this->getDaysPerWeek(FOR_SPOUSE)) / 10; //couple ratio
			else
				$days_per_week_ratio = $this->getDaysPerWeek(FOR_USER) / 5; //singles ratio
				
			//calc additional housing
			return round(max( 0, ($monthly_housing - $maxhousingmfb) ));
		}
		
		return 0;
	}
	
	
	//getClaimableMfb:	Calculates a persons claimable mfbs (the mfbs that are left after your housing has been taken out)
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your pretax super or the spouses, 1, pretax super
	//returns			claimable mfb (a number >= 0)
	public function getClaimableMfb($me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		if (isset($this->financial_data[$prefix.'MAX_MFB'])){
			if (isset($this->financial_data['MAX_MFB']) && isset($this->financial_data['HOUSING'])){
				//grab max housing mfb
				$max_housing_mfb = $this->getMaxHousingMfb();
				
				//make sure housing is monthly
				$monthly_housing = $this->getMonthlyHousing();
				
				//calcs spouses claimable mfb if possible
				if ($me_or_spouse){
					if(isset($this->financial_data['S_MAX_MFB'])){
						//calcs spouse claimable mfb (take my mfb off housing first and if there is housing still to be coverd take it from spouse mfb)
						return max(0, $this->financial_data['S_MAX_MFB'] - max(0, min($monthly_housing, $max_housing_mfb) - $this->financial_data['MAX_MFB']));
					} else {
						//if no spouse stipend so cant calc this value so return 0
						return 0;
					}
				}
				//calcs single claimable mfb otherwise
				return max(0, $this->financial_data['MAX_MFB'] - min($monthly_housing, $max_housing_mfb));
			} else {
				//if no housing to take away, just return max mfb
				return $this->financial_data[$prefix.'MAX_MFB'];
			}
		}
		return 0;
	}
	*/
	
	
	/*
	//getHousingStipend:	Calculates and returns the housing stipend (the amount of housing that is not covered by mfbs or
	//						additional housing allowance and needs to be covered by stipend)
	//params:				n/a
	//returns				housing_stipend (a number >= 0)
	public function getHousingStipend(){
		//calc housing stipend (diff between housing and what your mfbs & additional housing allowance will cover)
		if (isset($this->financial_data['HOUSING']) && isset($this->financial_data['STIPEND']) && isset($this->financial_data['ADDITIONAL_HOUSING']) && !($this->financial_data['overseas'] && !$this->financial_data['home_assignment']))
		{
			$monthly_housing = $this->getMonthlyHousing();
		
			if (isset($this->financial_data['S_STIPEND'])){
				if ($this->DEBUG) fb($this->financial_data);
			
				//calc lastest taxable income for me
				$annum = $this->financial_data['STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];
				$taxableincome = $this->calculateTaxableIncome($annum);
				//calc lastest taxable income for spouse
				$s_annum = $this->financial_data['S_STIPEND'] + $this->financial_data['S_POST_TAX_SUPER'] + $this->financial_data['S_ADDITIONAL_TAX'];
				$s_taxableincome = $this->calculateTaxableIncome($s_annum);
				
				//mfb rate for me
				$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
				//mfb rate for spouse
				$s_mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
				
				//calc lastest joint max mfbs
				$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
				$s_maxmfb = $this->calculateMaxMFB($s_taxableincome, $s_mfbrate, $this->getDaysPerWeek(1));
				$joint_maxmfb = $maxmfb + $s_maxmfb;
			
				//calc housing stipend from lastest values
				$housing_stipend = max(0, $monthly_housing - $joint_maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
				//if there is a housing stipend it needs to be split between stipend and mfbs
				//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
				if ($housing_stipend > 0){
					//recalc taxable income and max mfb with the calc housing stipend
					$annum += $housing_stipend;
					$taxableincome = $this->calculateTaxableIncome($annum);
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
					$joint_maxmfb = $maxmfb + $s_maxmfb;

					//max housing mfb
					$max_housing_mfb = $this->getMaxHousingMfb();

					$diff = min($max_housing_mfb, $monthly_housing) - $joint_maxmfb - $housing_stipend;

					//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
					$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;

					//calc the ideal tax from the ideal taxable income
					$ideal_tax = $this->calculateTax($ideal_taxable_income);

					//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
					$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
				}
				
				return round($housing_stipend);
			} else {
				//makes sure that a person without a stipend for there spouse is actually single
				if (!$this->spouse){
					//calc lastest taxable income
					$annum = $this->financial_data['STIPEND'] + $this->financial_data['POST_TAX_SUPER'] + $this->financial_data['ADDITIONAL_TAX'];
					$taxableincome = $this->calculateTaxableIncome($annum);
					
					//mfb rate
					$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
					
					//calc lastest max mfbs
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));
				
					//calc housing stipend from lastest values
					$housing_stipend = max(0, $monthly_housing - $maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
					//if there is a housing stipend it needs to be split between stipend and mfbs
					//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
					if ($housing_stipend > 0){
						//recalc taxable income and max mfb with the calc housing stipend
						$annum += $housing_stipend;
						$taxableincome = $this->calculateTaxableIncome($annum);
						$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(0));

						$max_housing_mfb = $this->getMaxHousingMfb();
	
						$diff = min($max_housing_mfb, $monthly_housing) - $maxmfb - $housing_stipend;
	
						//split diff between taxable income and mfbs and take taxable incomes portion of diff away. This will give the ideal taxable income.
						$ideal_taxable_income = ((1 - ($mfbrate*0.5)) * $diff) + $taxableincome;
	
						//calc the ideal tax from the ideal taxable income
						$ideal_tax = $this->calculateTax($ideal_taxable_income);
	
						//calc what housing stipend should be by taking everything other than housing stipend from the ideal taxable income
						$housing_stipend = $ideal_taxable_income - $ideal_tax - $this->financial_data['ADDITIONAL_TAX'] - $this->financial_data['POST_TAX_SUPER'] - $this->financial_data['STIPEND'];
					}
					
					return round($housing_stipend);
				}
			}
		}
		
		return 0;
	}
	*/
	
	//////////////////////Should grab these numbers from the DB////////////////////////
	
	
	/*
	//getMonthlyHousing:	Uses the housing frequency to return the housing amount as a monthly figure
	//params:				n/a
	//returns				monthly_housing (a number >= 0)
	public function getMonthlyHousing() {
		//convert fornightly housing to monthly housing
		if ($this->financial_data['HOUSING_FREQUENCY'] == 1)
			return round($this->financial_data['HOUSING'] * 26 / 12);
		else
			return $this->financial_data['HOUSING'];
	}
	
	
	//getPreTaxSuper:	Calculates a persons pretax super and returns it
	//params:			$mfbrate		- (a number 0-1) give it the rate to multiply it by
	//					$me_or_spouse	- (number 0 or 1) tells function wether you want your pretax super or the spouses pretax super
	//returns			pretax super (a number > 0)
	public function getPreTaxSuper($mfbrate, $me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse){$prefix = "S_"; $little_prefix = "s_"; } else {$prefix = ""; $little_prefix = "";}
	
		//Pre Tax Super (if its not set then set it to the min)
		if ($this->financial_data[$little_prefix.'pre_tax_super_mode'] == 'auto'){
			return round($this->financial_data[$prefix.'TAXABLE_INCOME'] * $mfbrate * $this->constants['MIN_ADD_SUPER_RATE']);
		} else {
			$min_pre_tax_super = round($this->financial_data[$prefix.'TAXABLE_INCOME'] * $mfbrate * $this->constants['MIN_ADD_SUPER_RATE']);
			if (!isset($this->financial_data[$prefix.'PRE_TAX_SUPER']) || $this->financial_data[$prefix.'PRE_TAX_SUPER'] < $min_pre_tax_super){
				return $min_pre_tax_super;
			}
		}
		//if manual mode then return manually entered value
		return $this->financial_data[$prefix.'PRE_TAX_SUPER'];
	}
	*/
	
	//getSpouseGuid:		If there is a spouse it will return their guid (a 32 char string that identifies a user in the database)
	//params:				n/a
	//returns				guid (a 32 char string that identifies a user in the database) (0 means not found in DB)
	public function getSpouseGuid(){
		//if there is a spouse get the guid else return 0
		if ($this->spouse){
			//get the spouse's guid from the DB
			$sql = mysql_query("SELECT SPOUSE_GUID FROM User_Profiles WHERE guid='".$this->guid."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				return $row['SPOUSE_GUID'];
			}
		}
		//if the spouse guid was not found return 0
		return 0;
	}
	
	
	
	public function setGuid($guid) {
		$this->guid = $guid;
	}
	
	
	
	public function setSpouseGuid($guid) {
		$this->spouse = $guid;
	}
	
	//getDaysPerWeek:	Takes a guid and grabs that persons days per week from the database
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your values or the spouses, 1, values
	//returns			days_per_week (a number 0,1,2,3,4,5) (0 means not found in DB)
	public function getDaysPerWeek($user_or_spouse){
		//grabs data from correct class variables based on if we are dealing with me or spouse
		if ($user_or_spouse == FOR_USER) {$guid = $this->guid; $days_per_week = $this->days_per_week;} else {$guid = $this->spouse; $days_per_week = $this->s_days_per_week;}
		//grabs it from DB only if it has not already been grabed (ie when its not its default value of 0)
		if ($days_per_week == 0){
			//Fetch the user's days per week
			$sql = mysql_query("SELECT DAYS_PER_WEEK, FT_PT_OS FROM User_Profiles WHERE guid='".$guid."'");
			if (mysql_num_rows($sql) == 1) {
				$row = mysql_fetch_assoc($sql);
				if ($row['FT_PT_OS'] == 0){
					$days_per_week = 5;
				} else {
					$days_per_week = $row['DAYS_PER_WEEK'] + 1; //+1 because days per week is stored as an index not a number
				}
			}
		}
		//saves data to correct class variables based on if we are dealing with me or spouse
		if ($user_or_spouse == FOR_USER) $this->days_per_week = $days_per_week; else $this->s_days_per_week = $days_per_week;
		return $days_per_week;//zero means not found in DB
	}
	
	public function setFinancialData($findat){
		$this->financial_data = $findat;
	}

}
?>