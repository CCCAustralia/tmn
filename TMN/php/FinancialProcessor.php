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

define("FUTURE_INVESTMENT_MODE_PRE_TAX", 0);
define("FUTURE_INVESTMENT_MODE_MORTGAGE", 1);
define("FUTURE_INVESTMENT_MODE_TAXABLE", 2);

define("MFB_RATE_ZERO", 0);
define("MFB_RATE_HALF", 1);
define("MFB_RATE_FULL", 2);

//Authenticate the user in GCX with phpCAS
if ( !isset($CAS_CLIENT_CALLED) ) {
	phpCAS::client(CAS_VERSION_2_0,'thekey.me',443,'cas');	//initialise phpCAS
	$CAS_CLIENT_CALLED = 1;
}
if (!phpCAS::isAuthenticated()) //if your not logged into gcx quit
	die('{success: false}');

class FinancialProcessor {
	
	protected $constants;
	
	//tax values
	//formula and values grabbed from:
	//Statement of formulas for calculating amounts to be withheld
	
	//Scale 7 (Where payee not eligible to receive leave loading and has claimed tax-free threshold)
	protected $x_resident;
				
	protected $a_resident;
				
	protected $b_resident;
				
	//Scale 3 (Foreign Residents)
	protected $x_non_resident;
				
	protected $a_non_resident;
	
	protected $b_non_resident;
	
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
		//////////  SET UP CONSTANTS  //////////
		include_once('classes/TmnConstants.php');
		$this->constants = getConstants(getVersionNumber());
		
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
		
		if ($this->financial_data['OS_RESIDENT_FOR_TAX_PURPOSES']) {
			$this->x = $this->constants['x_resident'];
			$this->a = $this->constants['a_resident'];
			$this->b = $this->constants['b_resident'];
		} else {
			$this->x = $this->constants['x_non_resident'];
			$this->a = $this->constants['a_non_resident'];
			$this->b = $this->constants['b_non_resident'];
		}
		if($this->DEBUG) fb($this->x);
	}
	
	
	//proc:				processes the financial data and returns the result as a json object
	//params:			n/a
	//returns			a string that describes a json object (will contain {"success": "true", "financial_data": ... } or {"success": "false", "err": ... }
	public function process() {
	
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
		$this->financial_data['ADDITIONAL_HOUSING']		= $this->getAdditionalHousing();
		$this->financial_data['ADDITIONAL_MORTGAGE']	= 0;
		
		//Spouse Taxable Income Panel
		if ($this->spouse) {
			if (isset($this->financial_data['S_STIPEND'])){
				$taxable_components	= $this->calculateTaxableIncomeComponentsForAussie(	$this->financial_data['S_STIPEND'],
																						$this->financial_data['S_POST_TAX_SUPER'],
																						$this->financial_data['S_ADDITIONAL_TAX'],
																						$this->getMfbRate($this->financial_data['S_MFB_RATE']),
																						$this->financial_data['S_FUTURE_INVESTMENT_MODE'],
																						$this->constants);
			
				//Copy across taxable components
				$this->financial_data['S_HOUSING_STIPEND']				= 0;
				$this->financial_data['S_TAXABLE_FUTURE_INVESTMENT']	= $taxable_components['future_investment'];
				$this->financial_data['S_NET_STIPEND']					= $taxable_components['net_stipend'];
				$this->financial_data['S_TAXABLE_INCOME']				= $taxable_components['taxable_income'];
				
				//calc tax and super from taxable income
				$this->financial_data['S_TAX']							= $this->calculateTax($this->financial_data['S_TAXABLE_INCOME']);
				$this->financial_data['S_EMPLOYER_SUPER']				= $this->calculateEmployerSuper($this->financial_data['S_TAXABLE_INCOME']);
	
				//check min stipend
				$err .= $this->validateStipend(FOR_SPOUSE);//0 means STIPEND, 1 means S_STIPEND
			}
			
			//Spouse Maximum MFB && Pre Tax Super
			if (isset($this->financial_data['S_TAXABLE_INCOME'])) {
			
				//enumerate mfb rate
				$mfbrate = $this->getMfbRate($this->financial_data['S_MFB_RATE']);
				
				//Spouse Pre Tax Super (if its not set then set it to the min)
				$this->financial_data['S_PRE_TAX_SUPER']		= $this->getPreTaxSuper($mfbrate, $this->financial_data['S_FUTURE_INVESTMENT_MODE'], FOR_SPOUSE); //the 1 means return spouse value
				
				//Additional Mortgage payments
				$this->financial_data['ADDITIONAL_MORTGAGE']	+= $this->getAdditionalMortgage($this->financial_data['S_TAXABLE_INCOME'],
																								$this->getMfbRate($this->financial_data['S_MFB_RATE']),
																								$this->financial_data['S_FUTURE_INVESTMENT_MODE'],
																								$this->constants);
			
				//Fetch the user's days per week
				$this->financial_data['S_DAYS_PER_WEEK']		= $this->getDaysPerWeek(FOR_SPOUSE);
				
				$this->financial_data['S_MAX_MFB']				= round($this->calculateMaxMFB($this->financial_data['S_TAXABLE_INCOME'], $mfbrate, $this->financial_data['S_DAYS_PER_WEEK']));
				
				//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
				$this->financial_data['S_CLAIMABLE_MFB']		= $this->getClaimableMfb(FOR_SPOUSE);//1 for spouse claimable mfb
			}
		}
		
		//Taxable Income Panel
		if (isset($this->financial_data['STIPEND'])){
			
			fb($this->financial_data['S_MAX_MFB']);
			$housing	= (isset($this->financial_data['S_MAX_MFB']) ? max(0, $this->getMonthlyHousing() - $this->financial_data['S_MAX_MFB']) : $this->getMonthlyHousing());
			fb('params');
			fb($this->financial_data['STIPEND']);
			fb($this->financial_data['POST_TAX_SUPER']);
			fb($this->financial_data['ADDITIONAL_TAX']);
			fb($housing);
			fb($this->financial_data['ADDITIONAL_HOUSING']);
			fb($spouse_max_mfbs);
			fb($this->getMfbRate($this->financial_data['MFB_RATE']));
			fb($this->getDaysPerWeek(FOR_USER));
			fb($this->financial_data['FUTURE_INVESTMENT_MODE']);
			fb($this->constants);
			
			$taxable_components	= $this->calculateTaxableIncomeComponentsForAussie(	$this->financial_data['STIPEND'],
																					$this->financial_data['POST_TAX_SUPER'],
																					$this->financial_data['ADDITIONAL_TAX'],
																					$this->getMfbRate($this->financial_data['MFB_RATE']),
																					$this->financial_data['FUTURE_INVESTMENT_MODE'],
																					$this->constants);
			
			//Copy across taxable components
			$this->financial_data['HOUSING_STIPEND']			= 0;
			$this->financial_data['TAXABLE_FUTURE_INVESTMENT']	= $taxable_components['future_investment'];
			$this->financial_data['NET_STIPEND']				= $taxable_components['net_stipend'];
			$this->financial_data['TAXABLE_INCOME']				= $taxable_components['taxable_income'];
			
			//calc tax and super from taxable income
			$this->financial_data['TAX']						= $this->calculateTax($this->financial_data['TAXABLE_INCOME']);
			$this->financial_data['EMPLOYER_SUPER']				= $this->calculateEmployerSuper($this->financial_data['TAXABLE_INCOME']);

			//check min stipend
			$err .= $this->validateStipend(FOR_USER);//0 means STIPEND, 1 means S_STIPEND
			
		}
		
		//Maximum MFB & Pre-tax Super
		if (isset($this->financial_data['TAXABLE_INCOME'])) {
		
			//mfb rate
			$mfbrate = $this->getMfbRate($this->financial_data['MFB_RATE']);
		
			//Pre Tax Super (if its not set then set it to the min)
			$this->financial_data['PRE_TAX_SUPER']			= $this->getPreTaxSuper($mfbrate, $this->financial_data['FUTURE_INVESTMENT_MODE'], FOR_USER);//the 0 means return my value
			
			//Additional Mortgage payments
			$this->financial_data['ADDITIONAL_MORTGAGE']	+= $this->getAdditionalMortgage($this->financial_data['TAXABLE_INCOME'],
																							$this->getMfbRate($this->financial_data['MFB_RATE']),
																							$this->financial_data['FUTURE_INVESTMENT_MODE'],
																							$this->constants);
			
			//Fetch Days Per Week
			$this->financial_data['DAYS_PER_WEEK']			= $this->getDaysPerWeek(FOR_USER);
			
			//calc max mfbs
			$this->financial_data['MAX_MFB']				= round($this->calculateMaxMFB($this->financial_data['TAXABLE_INCOME'], $mfbrate, $this->financial_data['DAYS_PER_WEEK']));
			
			$mfbsAvailableForHousing	= $this->financial_data['MAX_MFB'];
			$mfbsAvailableForHousing	+= (isset($this->financial_data['S_MAX_MFB']) ? $this->financial_data['S_MAX_MFB'] : 0);
			$additionalHousingAllowance	= (isset($this->financial_data['ADDITIONAL_HOUSING']) ? $this->financial_data['ADDITIONAL_HOUSING'] : 0);
			$housingFromMfbs			= $this->getMonthlyHousing() - $additionalHousingAllowance;
			
			if ( $housingFromMfbs > $mfbsAvailableForHousing ) {
				
				if ($mfbrate == 0) {
					
					$advisement = "Please decrease your housing. You are $" . ( $this->getMonthlyHousing() - $mfbsAvailableForHousing ) . " short.";
					
				} else {
					
					$advisement = "Please either increase your stipend or decrease your housing. You are $" . ( $housingFromMfbs - $mfbsAvailableForHousing ) . " short. So increase your stipend by about " . ( ( $housingFromMfbs - $mfbsAvailableForHousing ) / ( 1 + $mfbrate ) ) . ". Or decrease your housing by " . ( $this->getMonthlyHousing() - $mfbsAvailableForHousing ) . ".";
					
				}
				
				//$err					.= "\"HOUSING\":\"You do not have enough MFBs to cover your housing. $advisement\", ";;
				$warnings['HOUSING']	= "You do not have enough MFBs to cover your housing. $advisement";
				
			}
			
			//calc claimable mfbs (the mfbs that are left after your housing has been taken out)
			$this->financial_data['CLAIMABLE_MFB']			= $this->getClaimableMfb(FOR_USER);//0 for my claimable mfb
		}
		
		
		//Additional Mortgage
		if ($this->financial_data['ADDITIONAL_MORTGAGE'] > 0) {
			
			$this->financial_data['TOTAL_HOUSING']	= $this->getMonthlyHousing() + $this->financial_data['ADDITIONAL_MORTGAGE'];
			
		} else {
			
			$this->financial_data['TOTAL_HOUSING']	= 0;
			
		}
		
		
	
		if ($this->DEBUG) fb($this->financial_data);
		
		if ($err == '') {
		
			$result						= array('success'=>true);
			$result['financial_data']	= $this->financial_data;
			$result['warnings']			= $warnings;
			if($this->DEBUG) fb($result);
			return json_encode($result);
		}
		else {
			$result				= array('success'=>false);
			$result['errors']	= json_decode('{'.trim($err,", ").'}');
			$result['warnings']	= $warnings;
			return json_encode($result);
			//return '{"success": false, "errors":{'.trim($err,", ").'} }';	//Return with errors
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
	

	/**
	 * Calculates the user's future investment, housing stipend, net stipend and taxable income from the data the user entered.
	 * 
	 * Calculates these values by iteratively calculating the amount of future invesment and tax that needs to be added from any amounts that were added in the last iteration.
	 * Repeating this process results in the series converging on an optimal solution.
	 * 
	 * @param int $stipend
	 * @param int $post_tax_super
	 * @param int $additional_tax
	 * @param int $monthly_housing
	 * @param int $additional_housing_allowance
	 * @param float $mfb_rate
	 * @param int $days_per_week
	 * @param int $future_investment_mode
	 * @param assoc array $constants - must include $constants['MIN_ADD_SUPER_RATE']
	 * @return array( "future_investment" => int, "housing_stipend" => int, "net_stipend" => int, "taxable_income" => int )
	 */
	public function calculateTaxableIncomeComponentsForAussie($stipend, $post_tax_super, $additional_tax, $mfb_rate, $future_investment_mode, $constants) {

		if ($future_investment_mode == FUTURE_INVESTMENT_MODE_TAXABLE) {
			
			//init variables
			$futureInvestment		= array();
			$futureInvestmentTax	= array();
			$grossStipend			= array();
			$tax					= array();
			$iterations				= 0;
			$maxIterations			= 301;
				////////Set Initial Conditions For Iterative Series////////
			
			//the initial gross stipend is just the sum of the taxable values entered by the user
			$grossStipend[0]		= $stipend + $post_tax_super + $additional_tax;
			$grossStipendSum		= $grossStipend[0];
			//the initial tax is the tax on the initial gross stipend
			$tax[0]					= $this->calculateTaxFromWage( $grossStipend[0] );
			$taxSum					= $tax[0];
			
			//the initial future investment is the super rate applied to the initial taxable income (gross income + tax)
			$futureInvestment[0]	= $constants['MIN_ADD_SUPER_RATE'] * $mfb_rate * ( $grossStipend[0] + $tax[0] );
			//calculate tax on the initial future investment
			//(Note: To calculate tax on a particular segment you need calculate the tax on everything with the segment minus the tax on everything without the segment ie tax(everythingWithoutSegment + segment) - tax(everythingWithoutSegment))
			$futureInvestmentTax[0]	= $this->calculateTaxFromWage( $grossStipend[0] + $futureInvestment[0] ) - $this->calculateTaxFromWage( $grossStipend[0] );
			
				//////////Loop Through Calculating Each Term In Series/////////
			
			
			//The following loop will seek to at each iteration calculate the future investment and tax that needs to be added.
			//This is because in the previous iteration a bit more future investment and tax were added and the future investment and tax were those bits have not yet been added.
			//Adding the future investment and tax for consecutive iterations that get smaller and smaller will result in the optimum result being converged upon.
			//This process will continue until an acceptable accuracy has been achieved (After 300 iterations no more accuracy can be achieved due to the limits of a php variable).
			for ($iterations = 1; $iterations < $maxIterations; $iterations++) {
				
				//the gross stipend for this iteration is the little bit of future investment and housing stipend calculated in the last iteration
				$grossStipend[$iterations]				=	$futureInvestment[$iterations - 1];
				$grossStipendSum						+=	$grossStipend[$iterations];
				//the tax for this iteration is the tax for the future investment from the last iteration plus the tax for the housing stipend from the last iteration
				$tax[$iterations]						=	$futureInvestmentTax[$iterations - 1];
				$taxSum									+=	$tax[$iterations];
				
				//calculate the future investment for the last iteration
				$futureInvestment[$iterations]		=	$constants['MIN_ADD_SUPER_RATE'] * $mfb_rate * ( $grossStipend[$iterations] );
				//and calculate the tax on the future investment for this iteration
				$futureInvestmentTax[$iterations]	=	$this->calculateTaxFromWage( $grossStipendSum + $futureInvestment[$iterations] ) - $this->calculateTaxFromWage( $grossStipendSum );
	
			}
			
			
				/////////Calculate Required Values From Series///////////
			
			
			//add up all the iterations to get the final values for future investment and housing stipend
			for ($iterations = 0; $iterations <= $maxIterations; $iterations++) {
				
				$futureInvestmentTotal				+=	$futureInvestment[$iterations];
				
			}
			
			//calculate the required return values from the final values
			$netStipendTotal						= $stipend + $futureInvestmentTotal;
			$grossStipendTotal						= $grossStipend[0] + $futureInvestmentTotal + $housingStipendTotal;
			$taxableIncomeTotal						= $this->calculateTaxableIncome($grossStipendTotal);
		
		} else {
			
			$futureInvestmentTotal					= 0;
			$netStipendTotal						= $stipend;
			$grossStipendTotal						= $stipend + $post_tax_super + $additional_tax;
			$taxableIncomeTotal						= $this->calculateTaxableIncome($grossStipendTotal);
			
		}
		
		return array(
			"future_investment"	=> floor($futureInvestmentTotal),
			"net_stipend"		=> floor($netStipendTotal),
			"taxable_income"	=> floor($taxableIncomeTotal)
		);
		
	}
	
	
	//calculateEmployerSuper:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:					$taxableincome - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//returns					employer super (a whole number >= 0)
	public function calculateEmployerSuper($taxableincome){
		return	round($taxableincome * $this->constants['MIN_SUPER_RATE']);
	}
	
	
	//calculateMaxMFB:			Calculates and returns the max mfbs a person can take
	//params:					$taxableincome - (a whole number > 0) taxable income (freq doen't matter can take weekly, monthly, annually, etc)
	//							$mfbrate - (a decimal number 0 <= $mfbrate <= 1) the rate of mfbs they are entitled to (atm they are 0% 25% 50% of your taxable income)
	//							$daysperweek - (a whole number 1,2,3,4,5)
	//returns					maxmfb (a whole number >= 0)
	public function calculateMaxMFB($taxableincome, $mfbrate, $daysperweek) {
		
		$maxmfb = $taxableincome;
		
		$maxmfb = $maxmfb * ($mfbrate);
		
		//$maxmfb = $maxmfb * ($daysperweek / 5);
		
		return $maxmfb;
	}
	
	
	//calculateMaxWage:					Takes an index of the weekly tax ranges and returns the range for a wage
	//params:							$index - (an int >= 0) the index of a range in the $this->x variable
	//returns							weekly max wage (a whole number >= 0)
	public function calculateMaxWage($index) {
		//formula and values grabed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//the max taxable income - the tax of max taxable income gives us the max wage for that tax bracket
		return $this->x[$index] - round($this->a[$index] * (floor($this->x[$index]) + 0.99) - $this->b[$index]);
	}
	
	
	//calculateTaxableIncome:			Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly taxable income (a number >= 0)
	public function calculateTaxableIncome($wage){
		return $wage + $this->calculateTaxFromWage($wage);
	}
	
	
	//calculateTaxFromWage:				Takes a wage and returns a taxable income
	//params:							$wage - (a whole number > 0) the monthly wage
	//returns							monthly tax (a whole number >= 0)
	public function calculateTaxFromWage($wage) {
		
		if ($wage <= 0) {
			return 0;
		}
		
		//formula and values derived from:
		//Statement of formulas for calculating amounts to be withheld
	//if($this->DEBUG) fb("wage: ".$wage);
		//convert from months to weeks
		$wage = floor(floor($wage) * 12 / 52);
		
		for( $rangeCount = 0; $rangeCount < count($this->x); $rangeCount++ ){
			if ($wage < $this->calculateMaxWage($rangeCount))
				break;
		}
	//if($this->DEBUG) fb("rangecount: ".$rangeCount);
		return round(ceil(($this->a[$rangeCount] * ($wage) - $this->b[$rangeCount]) / (1 - $this->a[$rangeCount])) * 52 / 12);
	}
	
	
	//calculateTax:						Takes a monthly taxable income and returns the tax (formula and values grabed from: Statement of formulas for calculating amounts to be withheld. On the ATO website)
	//params:							$taxableincome - (a whole number > 0) the weekly taxable income
	//returns							monthly tax (a whole number >= 0)
	public function calculateTax($taxableincome) {
		
		if ($taxableincome <= 0) {
			return 0;
		}
		
		//formula and values grabbed from:
		//Statement of formulas for calculating amounts to be withheld
		
		//ATO rounding for monthly to weekly convertion (if $taxableincome ends with 33 cents then add one cent)
		if (($taxableincome-floor($taxableincome)) == 0.33) $taxableincome += 0.01;
		
		//convert from monthly to weekly
		$taxableincome = $taxableincome * 3 / 13; //same as $taxableincome = $taxableincome * 12 / 52
		
		//ATO rounding for weekly Tax calculation (ignore cents and add 0.99)
		$taxableincome = floor($taxableincome) + 0.99;
		
		//find which weekly tax bracket $taxableincome falls in
		for( $rangeCount = 0; $rangeCount < count($this->x); $rangeCount++ ){
			if ($taxableincome < $this->x[$rangeCount])
				break;
		}
		//calculate tax
		$tax = round($this->a[$rangeCount] * $taxableincome - $this->b[$rangeCount]);
		//convert back to monthly before returning
		return round($tax * 13 / 3); //same as $tax * 52 / 12
	}
	
	
	//getAdditionalHousing:	Calculates and returns the additional housing allowance (the amount of housing above the max housing mfb, set by Memeber Care)
	//params:				n/a
	//returns				additional_housing (a number >= 0)
	public function getAdditionalHousing(){
		if (isset($this->financial_data['HOUSING'])){
		
			//get max cut off
			$maxhousingmfb = $this->getMaxHousingMfb();
			
			//set housing freq to the default if not set
			if (!isset($this->financial_data['HOUSING_FREQUENCY'])) $this->financial_data['HOUSING_FREQUENCY'] = HOUSING_FREQUENCY_MONTHLY;
			
			//make sure housing is monthly
			$monthly_housing = $this->getMonthlyHousing();
			
			//get days per week ratio
			//TODO: apply it to the calculation
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
						//calcs spouse claimable mfb
						return max(0, $this->financial_data['S_MAX_MFB'] - min($monthly_housing, $max_housing_mfb));
					} else {
						//if no spouse stipend so cant calc this value so return 0
						return 0;
					}
					
				} else {
					
					if(isset($this->financial_data['S_MAX_MFB'])){
						//calcs my claimable mfb (take spouse mfb off housing first and if there is housing still to be coverd take it from my mfb)
						return max( 0, $this->financial_data['MAX_MFB'] - max( 0, min($monthly_housing, $max_housing_mfb) - $this->financial_data['S_MAX_MFB'] ) );
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
	
	//getDaysPerWeek:	Takes a guid and grabs that persons days per week from the database
	//params:			$me_or_spouse	- (number 0 or 1) tells function wether you, 0, want your values or the spouses, 1, values
	//returns			days_per_week (a number 0,1,2,3,4,5) (0 means not found in DB)
	public function getDaysPerWeek($me_or_spouse){
		//grabs data from correct class variables based on if we are dealing with me or spouse
		if ($me_or_spouse) {$guid = $this->spouse; $days_per_week = $this->s_days_per_week;} else {$guid = $this->guid; $days_per_week = $this->days_per_week;}
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
		if ($me_or_spouse) $this->s_days_per_week = $days_per_week; else $this->days_per_week = $days_per_week;
		return $days_per_week;//zero means not found in DB
	}
	
	
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
				$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(FOR_USER));
				$s_maxmfb = $this->calculateMaxMFB($s_taxableincome, $s_mfbrate, $this->getDaysPerWeek(FOR_SPOUSE));
				$joint_maxmfb = $maxmfb + $s_maxmfb;
			
				//calc housing stipend from lastest values
				$housing_stipend = max(0, $monthly_housing - $joint_maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
				//if there is a housing stipend it needs to be split between stipend and mfbs
				//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
				if ($housing_stipend > 0){
					//recalc taxable income and max mfb with the calc housing stipend
					$annum += $housing_stipend;
					$taxableincome = $this->calculateTaxableIncome($annum);
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(FOR_USER));
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
					$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(FOR_USER));
				
					//calc housing stipend from lastest values
					$housing_stipend = max(0, $monthly_housing - $maxmfb - $this->financial_data['ADDITIONAL_HOUSING']);
					//if there is a housing stipend it needs to be split between stipend and mfbs
					//we do this because as you increase stipend you increase mfbs and you decrease the housing stipend
					if ($housing_stipend > 0){
						//recalc taxable income and max mfb with the calc housing stipend
						$annum += $housing_stipend;
						$taxableincome = $this->calculateTaxableIncome($annum);
						$maxmfb = $this->calculateMaxMFB($taxableincome, $mfbrate, $this->getDaysPerWeek(FOR_USER));

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
	
	//getMaxHousingMfb:		It will return the max housing mfb based on if there is a spouse or not
	//						(this is the limit of how much of your mfbs will go toward housing)
	//params:				n/a
	//returns				max housing mfb (a number > 0)
	public function getMaxHousingMfb(){
		//update 2011 - fetch from database as a constant. was 960/1600c
		return $this->spouse ? $this->constants['MAX_HOUSING_MFB_COUPLES'] : $this->constants['MAX_HOUSING_MFB'];
	}
	
	
	//getMfbRate:		Takes an MFB_RATE index and returns the actual rate
	//params:			$MFB_RATE		- (number 0,1,2) give it the rate index
	//returns			mfbrate (a number 0-1)
	public function getMfbRate($MFB_RATE){
		//enumerate mfb rate
		switch ($MFB_RATE) {
			case MFB_RATE_ZERO:
			//Zero MFBs
				$mfbrate = 0;
				break;
			case MFB_RATE_HALF:
			//Half MFBs
				$mfbrate = 0.5;
				break;
			case MFB_RATE_FULL:
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
	
	
	//getMonthlyHousing:	Uses the housing frequency to return the housing amount as a monthly figure
	//params:				n/a
	//returns				monthly_housing (a number >= 0)
	public function getMonthlyHousing() {
		//convert fornightly housing to monthly housing
		if ($this->financial_data['HOUSING_FREQUENCY'] == HOUSING_FREQUENCY_FORTNIGHTLY) {
			return round($this->financial_data['HOUSING'] * 26 / 12);
		} else {
			return $this->financial_data['HOUSING'];
		}
	}
	
	
	//getPreTaxSuper:	Calculates a persons pretax super and returns it
	//params:			$mfbrate		- (a number 0-1) give it the rate to multiply it by
	//					$me_or_spouse	- (number 0 or 1) tells function wether you want your pretax super or the spouses pretax super
	//returns			pretax super (a number > 0)
	public function getPreTaxSuper($mfbrate, $future_investment_mode = 0, $me_or_spouse){
		
		//if the user has asked for no pre tax super then return 0
		if ($future_investment_mode > 0) {
			fb("no pre tax super");
			return 0;
			
		}
		
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
	
	public function getAdditionalMortgage($taxable_income, $mfb_rate, $future_investment_mode, $constants) {
		
		if ($future_investment_mode == FUTURE_INVESTMENT_MODE_MORTGAGE) {

			return round($taxable_income * $mfb_rate * $constants['MIN_ADD_SUPER_RATE']);
			
		} else {
			
			return 0;
			
		}
		
		
	}
	
	
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
	
	
	
	public function setFinancialData($findat){
		$this->financial_data = $findat;
	}
	
	
	//validateStipend:		Checks if stipend is valid, if not it returns an error message
	//params:				$me_or_spouse	- (number 0 or 1) tells function wether you want your values or your spouses values
	//returns				error message (a string, empty if valid)
	public function validateStipend($me_or_spouse){
		//changes wether it grabs your or spouse values (adds S_ prefix to keys)
		if ($me_or_spouse) $prefix = "S_"; else $prefix = "";
		
		//check min stipend
		if ($this->financial_data[$prefix.'NET_STIPEND'] < $this->constants['STIPEND_MIN']){
			if($this->financial_data[$prefix.'HOUSING_STIPEND'] > 0)
				return "\"".$prefix."NET_STIPEND\":\"Net Stipend is too low: must be at least $".$this->constants['STIPEND_MIN'].".\", ";
			else
				return "\"".$prefix."STIPEND\":\"Stipend is too low: must be at least $".$this->constants['STIPEND_MIN'].".\", ";
		}
		
		return "";
	}
}
?>