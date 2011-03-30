
Ext.ns('tmn', 'tmn.view');

/**
 * @class		tmn.view.PrintForm
 * 
 * <p>
 * <b>Description:</b> The Panel that lets the user view, print and submit their TMN.
 * </p>
 * 
 * @author		Michael Harrison	(<a href="mailto:michael.harrison@ccca.org.au">michael.harrison@ccca.org.au</a>)
 * 				& Thomas Flynn		(<a href="mailto:tom.flynn@ccca.org.au">tom.flynn@ccca.org.au</a>)
 * 
 * @namespace 	tmn.view
 * @extends		Ext.Panel
 * @version		TMN 2.1.0
 * @note		The TMN uses the MVC design structure, read up on it at <a href="http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller">http://en.wikipedia.org/wiki/Model-view-controller</a>).
 * @demo		http://mportal.ccca.org.au/TMN
 */
tmn.view.PrintForm = function(view, config) {
	/**
	 * @cfg {Object}	view			The object that defines the container that holds this form
	 * @note To be able to use this property you must pass it to the constructor when you create an instance of this class.
	 */
	this.view = view || {};					//the view that this form is contained in
	//set config variable to passed or default
	config = config || {};

	//set config options to passed or default
	/**
	 * @cfg {String}	id				The id parameter of the html tag that contains the form.<br />
	 * 									Default: 'print_form'
	 */
	this.id					=	config.id || 'print_form';
	/**
	 * @cfg {String}	title			The title displayed in the header of the form.<br />
	 * 									Default: 'Total Monthly Needs'
	 */
	this.title				=	config.title || 'Total Monthly Needs';
	/**
	 * @cfg {String}	load_url		The url of the server script that will process the forms load request.<br />
	 * 									Default: ''
	 */
	this.load_url			=	config.load_url || '';
	/**
	 * @cfg {String}	submit_url		The url of the server script that will process the forms submit request.<br />
	 * 									Default: 'php/submit_tmn.php'
	 */
	this.submit_url			=	config.submit_url || 'php/submit_tmn.php';
	/**
	 * @cfg {String}	aussie_form		Defines what type of user this form is intended for.
	 * 									If a user is not an Aussie Based Missionary they will not be able to view this form<br />
	 * 									Default: true
	 */
	this.aussie_form;
	/**
	 * @cfg {String}	overseas_form	Defines what type of user this form is intended for.
	 * 									If a user is not an Overseas Missionary they will not be able to view this form<br />
	 * 									Default: false
	 */
	this.overseas_form;
	(config.aussie_form === undefined) ? this.aussie_form = true : this.aussie_form = config.aussie_form;
	(config.overseas_form === undefined) ? this.overseas_form = true : this.overseas_form = config.overseas_form;
	
	//class wide variables definitions
	/**
	 * The users marital status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users marital status use the methods provided by this class.
	 */
	this.spouse='';
	
	/**
	 * The users overseas status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users overseas status use the methods provided by this class.
	 */
	this.overseas='';
	/**
	 * The TMN session that the user is working on. Is used by the backend to load, store and manipulate multiple sets of data for the user.
	 */
	this.session='';
	/**
	 * holds the response for each form after it is submitted successfully (accessed by 'international-assignment', 'home-assignment' or 'aussie-based')
	 * @type Associative array of Strings
	 */
	this.response={};
	/**
	 * holds the objects that are described by the json strings in response (accessed by 'international-assignment', 'home-assignment' or 'aussie-based')
	 * this varibale is filled by a call to {@link #processResponse}
	 * @type Associative array of Strings
	 */
	this.values={};
	/**
	 * holds the response for each form after it is submitted successfully (accessed by 'international-assignment', 'home-assignment' or 'aussie-based'
	 * (each of these is also has 'single' or 'spouse') ie templates['aussie-based']['single'])
	 * @type An Associative Array of Associative arrays of Strings
	 */
	this.templates = {};
	this.templates['aussie-based'] = {};	this.templates['international-assignment'] = {};	this.templates['home-assignment'] = {};	this.templates['international-assignment-auth'] = {};	this.templates['home-assignment-auth'] = {};

	//defines the aussie based template for displaying the TMN
	//ABS
this.templates['aussie-based']['single'] = new Ext.XTemplate('<!-- START aussie-based-single --><div class="tmn-page"><table><tr><td><h1 class="tmn-title">'+this.title+'</h1></td><td><img class="tmn-logo" src="http://www.ccca.org.au/tntmpd/TntMPDCCCALogo-new.JPG" alt="CCCA Logo" /></td></tr></table><table><tr><td style="width:50%;"><b>Name: </b>{firstname} {surname}</td><td style="width:25%;"><b>Date: </b>{date}</td><td style="width:25%;"><b>Support Account: </b>{fan}</td></tr><tr><td colspan="3"><div class="tmn-note"><b>Note:</b> From this year on your support account no. will have the form 101#### instead of 800####.</div></td></tr><tr><td colspan="3"><br /><br /></td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-goals"><th>Your Monthly Support Goal is: ${tmn}</th><th colspan="2">Your Account must be above: ${buffer}</th></tr><tr><td colspan="3"><hr /></td></tr></table><p>&nbsp;</p><div class="tmn-break-down"><div class="header">TMN Break Down</div><table><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Stipend (Money in your account)</th><td>${stipend}</td></tr><tr class="tmn-data"><th colspan="2">Housing Stipend (The amount of your stipend that will be used on housing)</th><td>${housing_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income ( Stipend + Housing Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">MFB\'s</th><td>${max_mfb}</td></tr><tpl if="additional_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Additional Housing Allowance</th><td>${additional_housing_allowance}</td></tr></tpl><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td>${total_transfers}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td>${workers_comp}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td>${ccca_levy}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td>${tmn}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-summary"><div class="header">Housing Summary</div><table><tr class="tmn-data"><th colspan="2">The amount that comes from your MFBs monthly</th><td>${housing_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from you Stipend monthly</th><td>${housing_stipend}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from Additional Housing Allowance monthly</th><td>${additional_housing}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Housing</th><td>${monthly_housing}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th style="text-align:center;" colspan="4">CCCA will pay ${housing} into your elected housing account {housing_frequency}.</th></tr></table></div></tpl><tpl if="mfb &gt; 0"><p>&nbsp;</p><div class="tmn-mfb-summary"><div class="header">Ministry Fringe Benifits Summary</div><table><tr><th colspan="2">MFB Rate</th><td>${mfb_rate}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that you can make claims from</th><td>${claimable_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that will go toward housing</th><td>${housing_mfb}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Ministry Fringe Benifits</th><td>${max_mfb}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td></tr><tpl if="additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><h2>Update Payment Methods</h2><p><u>If your bank or credit card details have changed</u></p><p>Please record them below.</p><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-payments"><div class="header">Housing Payments</div><table><tr><td><strong>Bank Account (preferred)</strong></td><td><strong>Cheque</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td><td><p>Name on Cheque:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p>__ __ __ / __ __ __</td><td><p>Postal Address:</p><p><br>___________________________________________</p><p><br>___________________________________________</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td><td><p>Reference (if needed):</p><p><br>___________________________________________</p></td></tr><tr><td>&nbsp;</td></tr><tr><td><strong>BPay:</strong></td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-stipend-payments"><div class="header">Stipend Payments</div><table><tr><td><strong>Bank Account</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p><p><br>__ __ __ /__ __ __</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td></tr></table></div><br /><div class="tmn-mfb-payments"><div class="header">MMR/MFB Reimbursements:</div><table><tr><td colspan="2"><strong>Credit Card</strong></td></tr><tr><td><p>Name on Card</p><p><br>___________________________________________</p></td><td><p><br><input type="checkbox" name="checkbox" value="checkbox"> Visa<br><input type="checkbox" name="checkbox" value="checkbox"> MasterCard</p></td></tr><tr><td><p>Financial Institution</p><p><br>___________________________________________</p></td><td>&nbsp;</td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div><p>&nbsp;</p><div class="tmn-authorisation"><div class="header">Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p class="footer">$Rev: 215 $| $Date: 2011-03-04 11:29:23 +1100 (Fri, 04 Mar 2011) $</p></div><!-- END aussie-based-single -->');
	//ABS
	
	//ABC
this.templates['aussie-based']['spouse'] = new Ext.XTemplate('<!-- START aussie-based-couple --><div class="tmn-page"><table><tr><td><h1 class="tmn-title">'+this.title+'</h1></td><td><img class="tmn-logo" src="http://www.ccca.org.au/tntmpd/TntMPDCCCALogo-new.JPG" alt="CCCA Logo" /></td></tr></table><div class="tmn-instructions"><div>- Print a copy for your own records.</div><div>- Have it authorised by the appropriate people.</div><div>- Submit your TMN to Payroll (PO Box 565, Mulgrave, Vic, 3170 OR payroll@ccca.org.au).</div><div class="indent">- If this is your first TMN, submit to Member Care (PO Box 565, Mulgrave, Vic, 3170 OR mc.admin@ccca.org.au).</div><div class="indent">- If you would like to submit it by email, please print this page as a pdf.</div><div class="double-indent">- You should then attach the pdf to an email to the appropriate people for authorisation.</div><div class="double-indent">- It should be forwarded up the authority chain (as needed) and then forwarded to payroll@ccca.org.au.</div><div class="double-indent">- It is your responsibility to see that it reaches payroll.</div><div class="double-indent">- If you need software to print your TMN as a PDF then <a href="http://www.cutepdf.com/download/CuteWriter.exe" target="_blank">download</a> Cute PDF.</div></div><table><tr><td style="width:50%;"><b>Name: </b>{firstname} {surname} &amp; {s_firstname} {s_surname}</td><td style="width:25%;"><b>Date: </b>{date}</td><td style="width:25%;"><b>Support Account: </b>{fan}</td></tr><tr><td colspan="3"><div class="tmn-note"><b>Note:</b> From this year on your support account no. will have the form 101#### instead of 800####.</div></td></tr><tr><td colspan="3"><br /><br /></td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-goals"><th>Your Monthly Support Goal is: ${tmn}</th><th colspan="2">Your Account must be above: ${buffer}</th></tr><tr><td colspan="3"><hr /></td></tr></table><p>&nbsp;</p><div class="tmn-break-down"><div class="header">TMN Break Down</div><table><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td><td>{s_firstname}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td><td>{s_ministry}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td><td>{s_ft_pt_os} - {s_days_per_wk} Days</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Net Stipend</th><td>${net_stipend}</td><td>${s_net_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td><td>${s_tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td><td>${s_additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income (Net Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td><td>${s_taxable_income}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">MFB\'s</th><td>${max_mfb}</td><td>${s_max_mfb}</td></tr><tpl if="additional_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Additional Housing Allowance</th><td>${additional_housing_allowance}</td><td>${s_additional_housing_allowance}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td><td>${s_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Joint Financial Package</th><td colspan="2">${joint_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td><td>${s_mmr}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td colspan="2">${workers_comp}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td colspan="2">${ccca_levy}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td colspan="2">${tmn}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div><p>&nbsp;</p><div class="tmn-stipend-summary"><div class="header">Stipend Summary</div><table><tr class="tmn-data"><th colspan="2">Stipend (Money in your account)</th><td>${stipend}</td><td>${s_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Housing Stipend (The amount of your stipend that will be used on housing)</th><td>${housing_stipend}</td><td>${s_housing_stipend}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Net Stipend (Stipend + Housing Stipend)</th><td>${net_stipend}</td><td>${s_net_stipend}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-summary"><div class="header">Housing Summary</div><table><tr class="tmn-data"><th colspan="2">The amount that comes from your MFBs monthly</th><td>${housing_mfb}</td><td>${s_housing_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from you Stipend monthly</th><td>${housing_stipend}</td><td>${s_housing_stipend}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from Additional Housing Allowance monthly</th><td colspan="2">${additional_housing}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Housing</th><td colspan="2">${monthly_housing}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th style="text-align:center;" colspan="4">CCCA will pay ${housing} into your elected housing account {housing_frequency}.</th></tr></table></div></tpl><tpl if="mfb &gt; 0 || s_mfb &gt; 0"><p>&nbsp;</p><div class="tmn-mfb-summary"><div class="header">Ministry Fringe Benifits Summary</div><table><tr><th colspan="2">MFB Rate</th><td>${mfb_rate}</td><td>${s_mfb_rate}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that you can make claims from</th><td>${claimable_mfb}</td><td>${s_claimable_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that will go toward housing</th><td>${housing_mfb}</td><td>${s_housing_mfb}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Ministry Fringe Benifits</th><td>${max_mfb}</td><td>${s_max_mfb}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td><td>${s_total_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td><td>${s_resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td><td>{s_super_fund}</td></tr><tpl if="additional_life_cover &gt; 0 || s_additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td><td>{s_income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><h2>Update Payment Methods</h2><p><u>If your bank or credit card details have changed</u></p><p>Please record them below.</p><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-payments"><div class="header">Housing Payments</div><table><tr><td><strong>Bank Account (preferred)</strong></td><td><strong>Cheque</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td><td><p>Name on Cheque:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p>__ __ __ / __ __ __</td><td><p>Postal Address:</p><p><br>___________________________________________</p><p><br>___________________________________________</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td><td><p>Reference (if needed):</p><p><br>___________________________________________</p></td></tr><tr><td>&nbsp;</td></tr><tr><td><strong>BPay:</strong></td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-stipend-payments"><div class="header">Stipend Payments</div><table><tr><td><strong>Bank Account</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p><p><br>__ __ __ /__ __ __</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td></tr></table></div><br /><div class="tmn-mfb-payments"><div class="header">MMR/MFB Reimbursements:</div><table><tr><td colspan="2"><strong>Credit Card</strong></td></tr><tr><td><p>Name on Card</p><p><br>___________________________________________</p></td><td><p><br><input type="checkbox" name="checkbox" value="checkbox"> Visa<br><input type="checkbox" name="checkbox" value="checkbox"> MasterCard</p></td></tr><tr><td><p>Financial Institution</p><p><br>___________________________________________</p></td><td>&nbsp;</td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div><p>&nbsp;</p><div class="tmn-authorisation"><div class="header">Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname} OR {s_firstname} {s_surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p class="footer">$Rev: 181 $| $Date: 2010-06-04 18:59:21 +1000 (Fri, 04 Jun 2010) $</p></div><!-- END aussie-based-couple -->');
	//ABC

	//defines the international assignmnet section of the template for displaying the TMN
	//IAS
this.templates['international-assignment']['single'] = new Ext.XTemplate('<!-- START international-assignment-single --><div class="tmn-page"><table><tr><td><h1 class="tmn-title">'+this.title+'</h1></td></tr></table><div class="tmn-instructions"><div>- Print a copy for your own records.</div><div>- Have it authorised by the appropriate people.</div><div>- Submit your TMN to Payroll (PO Box 565, Mulgrave, Vic, 3170 OR payroll@ccca.org.au).</div><div class="indent">- If this is your first TMN, submit to Member Care (PO Box 565, Mulgrave, Vic, 3170 OR mc.admin@ccca.org.au).</div><div class="indent">- If you would like to submit it by email, please print this page as a pdf.</div><div class="double-indent">- You should then attach the pdf to an email to the appropriate people for authorisation.</div><div class="double-indent">- It should be forwarded up the authority chain (as needed) and then forwarded to payroll@ccca.org.au.</div><div class="double-indent">- It is your responsibility to see that it reaches payroll.</div><div class="double-indent">- If you need software to print your TMN as a PDF then <a href="http://www.cutepdf.com/download/CuteWriter.exe" target="_blank">download</a> Cute PDF.</div></div><table><tr><td style="width:50%;"><b>Name: </b>{firstname} {surname}</td><td style="width:25%;"><b>Date: </b>{date}</td><td style="width:25%;"><b>Support Account: </b>{fan}</td></tr><tr><td colspan="3"><div class="tmn-note"><b>Note:</b> From this year on your support account no. will have the form 101#### instead of 800####.</div></td></tr><tr><td colspan="3"><br /><br /></td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-goals"><th>Your Monthly Support Goal is: ${tmn}</th><th colspan="2">Your Account must be above: ${buffer}</th></tr><tr><td colspan="3"><hr /></td></tr></table><p>&nbsp;</p><div class="tmn-international-break-down"><div class="header">TMN Break Down for International Assignment</div><table><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Dates of Assignment</th><td>{os_assignment_start_date} - {os_assignment_end_date}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Tax Status</th><td>{os_resident_for_tax_purposes}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Net Stipend</th><td>${net_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income (Net Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">LAFHA</th><td>${os_lafha}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td>${total_transfers}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td>${workers_comp}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td>${ccca_levy}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td>${tmn}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td></tr><tpl if="additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p></div><!-- END international-assignment-single -->');
	//IAS

	//IAC
this.templates['international-assignment']['spouse'] = new Ext.XTemplate('<!-- START international-assignment-couple --><div class="tmn-page"><table><tr><td><h1 class="tmn-title">'+this.title+'</h1></td></tr></table><div class="tmn-instructions"><div>- Print a copy for your own records.</div><div>- Have it authorised by the appropriate people.</div><div>- Submit your TMN to Payroll (PO Box 565, Mulgrave, Vic, 3170 OR payroll@ccca.org.au).</div><div class="indent">- If this is your first TMN, submit to Member Care (PO Box 565, Mulgrave, Vic, 3170 OR mc.admin@ccca.org.au).</div><div class="indent">- If you would like to submit it by email, please print this page as a pdf.</div><div class="double-indent">- You should then attach the pdf to an email to the appropriate people for authorisation.</div><div class="double-indent">- It should be forwarded up the authority chain (as needed) and then forwarded to payroll@ccca.org.au.</div><div class="double-indent">- It is your responsibility to see that it reaches payroll.</div><div class="double-indent">- If you need software to print your TMN as a PDF then <a href="http://www.cutepdf.com/download/CuteWriter.exe" target="_blank">download</a> Cute PDF.</div></div><table><tr><td style="width:50%;"><b>Name: </b>{firstname} {surname} &amp; {s_firstname} {s_surname}</td><td style="width:25%;"><b>Date: </b>{date}</td><td style="width:25%;"><b>Support Account: </b>{fan}</td></tr><tr><td colspan="3"><div class="tmn-note"><b>Note:</b> From this year on your support account no. will have the form 101#### instead of 800####.</div></td></tr><tr><td colspan="3"><br /><br /></td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-goals"><th>Your Monthly Support Goal is: ${tmn}</th><th colspan="2">Your Account must be above: ${buffer}</th></tr><tr><td colspan="3"><hr /></td></tr></table><p>&nbsp;</p><div class="tmn-international-break-down"><div class="header">TMN Break Down for International Assignment</div><table><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td><td>{s_firstname}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td><td>{s_ministry}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Dates of Assignment</th><td colspan="2">{os_assignment_start_date} - {os_assignment_end_date}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td><td>{s_ft_pt_os} - {s_days_per_wk} Days</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Tax Status</th><td colspan="2">{os_resident_for_tax_purposes}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Net Stipend</th><td>${net_stipend}</td><td>${s_net_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td><td>${s_tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td><td>${s_additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income (Net Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td><td>${s_taxable_income}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">LAFHA</th><td>${os_lafha}</td><td>${s_os_lafha}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td><td>${s_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Joint Financial Package</th><td colspan="2">${joint_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td><td>${s_mmr}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td colspan="2">${workers_comp}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td colspan="2">${ccca_levy}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td colspan="2">${tmn}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td><td>${s_total_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td><td>${s_resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td><td>{s_super_fund}</td></tr><tpl if="additional_life_cover &gt; 0 || s_additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td><td>{s_income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p></div><!-- END international-assignment-couple -->');
	//IAC

	//defines the home assignmnet section of the template for displaying the TMN
	//HAS
this.templates['home-assignment']['single'] = new Ext.XTemplate('<!-- START home-assignment-single --><div class="tmn-page"><div class="tmn-home-break-down"><div class="header">TMN Break Down for Home Assignment</div><table><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Dates of Assignment</th><td>{os_assignment_start_date} - {os_assignment_end_date}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Tax Status</th><td>{os_resident_for_tax_purposes}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Net Stipend</th><td>${net_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income (Net Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">MFB\'s</th><td>${max_mfb}</td></tr><tpl if="additional_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Additional Housing Allowance</th><td>${additional_housing_allowance}</td></tr></tpl><tpl if="os_overseas_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Overseas Housing Allowance</th><td>${os_overseas_housing_allowance}</td></tr></tpl><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td>${total_transfers}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td>${workers_comp}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td>${ccca_levy}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td>${tmn}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div><p>&nbsp;</p><div class="tmn-stipend-summary"><div class="header">Stipend Summary</div><table><tr class="tmn-data"><th colspan="2">Stipend (Money in your account)</th><td>${stipend}</td></tr><tr class="tmn-data"><th colspan="2">Housing Stipend (The amount of your stipend that will be used on housing)</th><td>${housing_stipend}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Net Stipend (Stipend + Housing Stipend)</th><td>${net_stipend}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-summary"><div class="header">Housing Summary</div><table><tr class="tmn-data"><th colspan="2">The amount that comes from your MFBs monthly</th><td>${housing_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from you Stipend monthly</th><td>${housing_stipend}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from Additional Housing Allowance monthly</th><td>${additional_housing}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Housing</th><td>${monthly_housing}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th style="text-align:center;" colspan="3">CCCA will pay ${housing} into your elected housing account {housing_frequency}.</th></tr><tr><td colspan="3"><hr /></td></tr><tr><th style="text-align:center;" colspan="3">CCCA will also give you ${os_overseas_housing} toward the cost of your housing outside of Australia.</th></tr><tr><td colspan="3"><hr /></td></tr></table></div></tpl><tpl if="mfb &gt; 0"><p>&nbsp;</p><div class="tmn-mfb-summary"><div class="header">Ministry Fringe Benifits Summary</div><table><tr><th colspan="2">MFB Rate</th><td>${mfb_rate}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that you can make claims from</th><td>${claimable_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that will go toward housing</th><td>${housing_mfb}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Ministry Fringe Benifits</th><td>${max_mfb}</td></tr><tr><td colspan="3"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td></tr><tr><td colspan="3"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td></tr><tpl if="additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><h2>Update Payment Methods</h2><p><u>If your bank or credit card details have changed</u></p><p>Please record them below.</p><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-payments"><div class="header">Housing Payments</div><table><tr><td><strong>Bank Account (preferred)</strong></td><td><strong>Cheque</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td><td><p>Name on Cheque:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p>__ __ __ / __ __ __</td><td><p>Postal Address:</p><p><br>___________________________________________</p><p><br>___________________________________________</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td><td><p>Reference (if needed):</p><p><br>___________________________________________</p></td></tr><tr><td>&nbsp;</td></tr><tr><td><strong>BPay:</strong></td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-stipend-payments"><div class="header">Stipend Payments</div><table><tr><td><strong>Bank Account</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p><p><br>__ __ __ /__ __ __</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td></tr></table></div><br /><div class="tmn-mfb-payments"><div class="header">MMR/MFB Reimbursements:</div><table><tr><td colspan="2"><strong>Credit Card</strong></td></tr><tr><td><p>Name on Card</p><p><br>___________________________________________</p></td><td><p><br><input type="checkbox" name="checkbox" value="checkbox"> Visa<br><input type="checkbox" name="checkbox" value="checkbox"> MasterCard</p></td></tr><tr><td><p>Financial Institution</p><p><br>___________________________________________</p></td><td>&nbsp;</td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div><p>&nbsp;</p></div><!-- END home-assignment-single -->');
	//HAS
	
	//HAC
this.templates['home-assignment']['spouse'] = new Ext.XTemplate('<!-- START home-assignment-couple --><div class="tmn-page"><div class="tmn-home-break-down"><div class="header">TMN Break Down for Home Assignment</div><table><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Name</th><td>{firstname}</td><td>{s_firstname}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Ministry</th><td>{ministry}</td><td>{s_ministry}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Dates of Assignment</th><td colspan="2">{os_assignment_start_date} - {os_assignment_end_date}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Status</th><td>{ft_pt_os} - {days_per_wk} Days</td><td>{s_ft_pt_os} - {s_days_per_wk} Days</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Tax Status</th><td colspan="2">{os_resident_for_tax_purposes}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Net Stipend</th><td>${net_stipend}</td><td>${s_net_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Estimated Tax </th><td>${tax}</td><td>${s_tax}</td></tr><tr class="tmn-data"><th colspan="2">Additional Tax </th><td>${additional_tax}</td><td>${s_additional_tax}</td></tr><tr class="tmn-data"><th colspan="2">Post-tax Super </th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Taxable Income (Net Stipend + Estimated Tax + Additional Tax + Post-Tax Super)</th><td>${taxable_income}</td><td>${s_taxable_income}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Pre-tax Super </th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Additional Life Cover (per month)</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr><tr class="tmn-data"><th colspan="2">MFB\'s</th><td>${max_mfb}</td><td>${s_max_mfb}</td></tr><tpl if="additional_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Additional Housing Allowance</th><td>${additional_housing_allowance}</td><td>${s_additional_housing_allowance}</td></tr></tpl><tpl if="os_overseas_housing &gt; 0"><tr class="tmn-data"><th colspan="2">Overseas Housing Allowance</th><td>${os_overseas_housing_allowance}</td><td>${s_os_overseas_housing_allowance}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Financial Package </th><td>${financial_package}</td><td>${s_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Joint Financial Package</th><td colspan="2">${joint_financial_package}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Employer Super </th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">MMR</th><td>${mmr}</td><td>${s_mmr}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">Worker\'s Compensation</th><td colspan="2">${workers_comp}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">CCCA Levy</th><td colspan="2">${ccca_levy}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Needs</th><td colspan="2">${tmn}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div><p>&nbsp;</p><div class="tmn-stipend-summary"><div class="header">Stipend Summary</div><table><tr class="tmn-data"><th colspan="2">Stipend (Money in your account)</th><td>${stipend}</td><td>${s_stipend}</td></tr><tr class="tmn-data"><th colspan="2">Housing Stipend (The amount of your stipend that will be used on housing)</th><td>${housing_stipend}</td><td>${s_housing_stipend}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Net Stipend (Stipend + Housing Stipend)</th><td>${net_stipend}</td><td>${s_net_stipend}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-summary"><div class="header">Housing Summary</div><table><tr class="tmn-data"><th colspan="2">The amount that comes from your MFBs monthly</th><td>${housing_mfb}</td><td>${s_housing_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from you Stipend monthly</th><td>${housing_stipend}</td><td>${s_housing_stipend}</td></tr><tr class="tmn-data"><th colspan="2">The amount that comes from Additional Housing Allowance monthly</th><td colspan="2">${additional_housing}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Monthly Housing</th><td colspan="2">${monthly_housing}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th style="text-align:center;" colspan="4">CCCA will pay ${housing} into your elected housing account {housing_frequency}.</th></tr><tr><td colspan="4"><hr /></td></tr><tr><th style="text-align:center;" colspan="4">CCCA will also give you ${os_overseas_housing} toward the cost of your housing outside of Australia.</th></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><tpl if="mfb &gt; 0 || s_mfb &gt; 0"><p>&nbsp;</p><div class="tmn-mfb-summary"><div class="header">Ministry Fringe Benifits Summary</div><table><tr><th colspan="2">MFB Rate</th><td>${mfb_rate}</td><td>${s_mfb_rate}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that you can make claims from</th><td>${claimable_mfb}</td><td>${s_claimable_mfb}</td></tr><tr class="tmn-data"><th colspan="2">The amount of your MFBs that will go toward housing</th><td>${housing_mfb}</td><td>${s_housing_mfb}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Ministry Fringe Benifits</th><td>${max_mfb}</td><td>${s_max_mfb}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-super-summary"><div class="header">Super Summary</div><table><tr class="tmn-data"><th colspan="2">Post Tax Super</th><td>${post_tax_super}</td><td>${s_post_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Pre Tax Super</th><td>${pre_tax_super}</td><td>${s_pre_tax_super}</td></tr><tr class="tmn-data"><th colspan="2">Employer Super</th><td>${employer_super}</td><td>${s_employer_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Super</th><td>${total_super}</td><td>${s_total_super}</td></tr><tr><td colspan="4"><hr /></td></tr><tr><th colspan="2">Reportable Employer Super Contribution (RESC)</th><td>${resc}</td><td>${s_resc}</td></tr><tr><th colspan="2">Super Choice</th><td>{super_fund}</td><td>{s_super_fund}</td></tr><tpl if="additional_life_cover &gt; 0 || s_additional_life_cover &gt; 0"><tr><th colspan="2">Amount of Additional Life Cover</th><td>${additional_life_cover}</td><td>${s_additional_life_cover}</td></tr></tpl><tr><th colspan="2">Additional Income Protection Premium paid from</th><td>{income_protection_cover_source}</td><td>{s_income_protection_cover_source}</td></tr></table></div><tpl if="total_transfers &gt; 0"><p>&nbsp;</p><div class="tmn-internal-transfer-summary"><div class="header">Internal Transfer Summary</div><table><tr><th colspan="2">Internal Transfers: </th><td>Name</td><td>Amount</td></tr><tpl for="transfers"><tr class="tmn-data"><th colspan="2"></th><td>{name}</td><td>${amount}</td></tr></tpl><tr><td colspan="4"><hr /></td></tr><tr class="tmn-total"><th colspan="2">Total Internal Transfers</th><td colspan="2">${total_transfers}</td></tr><tr><td colspan="4"><hr /></td></tr></table></div></tpl><p>&nbsp;</p><h2>Update Payment Methods</h2><p><u>If your bank or credit card details have changed</u></p><p>Please record them below.</p><tpl if="housing &gt; 0"><p>&nbsp;</p><div class="tmn-housing-payments"><div class="header">Housing Payments</div><table><tr><td><strong>Bank Account (preferred)</strong></td><td><strong>Cheque</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td><td><p>Name on Cheque:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p>__ __ __ / __ __ __</td><td><p>Postal Address:</p><p><br>___________________________________________</p><p><br>___________________________________________</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td><td><p>Reference (if needed):</p><p><br>___________________________________________</p></td></tr><tr><td>&nbsp;</td></tr><tr><td><strong>BPay:</strong></td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div></tpl><p>&nbsp;</p><div class="tmn-stipend-payments"><div class="header">Stipend Payments</div><table><tr><td><strong>Bank Account</strong></td></tr><tr><td><p>Name on Account:</p><p><br>___________________________________________</p></td></tr><tr><td><p>BSB:</p><p><br>__ __ __ /__ __ __</p></td></tr><tr><td><p>Account Number:</p><p><br>___________________________________________</p></td></tr></table></div><br /><div class="tmn-mfb-payments"><div class="header">MMR/MFB Reimbursements:</div><table><tr><td colspan="2"><strong>Credit Card</strong></td></tr><tr><td><p>Name on Card</p><p><br>___________________________________________</p></td><td><p><br><input type="checkbox" name="checkbox" value="checkbox"> Visa<br><input type="checkbox" name="checkbox" value="checkbox"> MasterCard</p></td></tr><tr><td><p>Financial Institution</p><p><br>___________________________________________</p></td><td>&nbsp;</td></tr><tr><td><p>BPay Reference Number (this may or may not be your card number):</p><p><br>__ __ __ __ - __ __ __ __ - __ __ __ __ - __ __ __ __</p></td><td><p>BPay Biller Code:</p><p><br>__ __ __ __ - __ __ __ __</p></td></tr></table></div><p>&nbsp;</p></div><!-- END home-assignment-couple -->');
	//HAC

	//defines the international assignmnet authorisation section of the template for displaying the TMN
	//IAAS
this.templates['international-assignment-auth']['single'] = new Ext.XTemplate('<!-- START international-assignment-auth-single --><div class="tmn-page"><div class="tmn-authorisation"><div class="header">International Assignment Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p>&nbsp;</p></div><!-- END international-assignment-auth-single -->');
	//IAAS
	
	//IAAC
this.templates['international-assignment-auth']['spouse'] = new Ext.XTemplate('<!-- START international-assignment-auth-couple --><div class="tmn-page"><div class="tmn-authorisation"><div class="header">International Assignment Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname} OR {s_firstname} {s_surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p>&nbsp;</p></div><!-- END international-assignment-auth-couple -->');
	//IAAC

	//defines the home assignmnet authorisation section of the template for displaying the TMN
	//HAAS
this.templates['home-assignment-auth']['single'] = new Ext.XTemplate('<!-- START home-assignment-auth-single --><div class="tmn-page"><div class="tmn-authorisation"><div class="header">Home Assignment Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p class="footer">$Rev: 181 $| $Date: 2010-06-04 18:59:21 +1000 (Fri, 04 Jun 2010) $</p></div><!-- END home-assignment-auth-single -->');
	//HAAS
	
	//HAAC
this.templates['home-assignment-auth']['spouse'] = new Ext.XTemplate('<!-- START home-assignment-auth-couple --><div class="tmn-page"><div class="tmn-authorisation"><div class="header">Home Assignment Authorisation</div><tpl if="auth_lv2 || auth_lv3"><div class="reasons"><tpl if="auth_lv2">Your TMN must be approved by your NML because:<tpl for="auth_lv2_reasons"><div class="indent">- {reason}</div></tpl></tpl><tpl if="auth_lv3"><br/>Your TMN must be approved by your MGL because:<tpl for="auth_lv3_reasons"><div class="indent">- {reason}</div></tpl></tpl></div></tpl><table><tr><td colspan="2"><b>Missionary:</b></td></tr><tr><td colspan="2">Name: {firstname} {surname} OR {s_firstname} {s_surname}<hr /></td></tr><tr><td style="width: 70%">Signature: <hr /></td><td style="width: 30%">Date: <hr /></td></tr><tpl if="auth_lv1"><tr><td colspan="2"><b>Ministry Overseer:</b></td></tr><tr><td colspan="2">Name:<hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv2"><tr><td colspan="2"><b>National Ministry Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl><tpl if="auth_lv3"><tr><td colspan="2"><b>Ministry Group Leader:</b></td></tr><tr><td colspan="2">Name: <hr /></td></tr><tr><td>Signature: <hr /></td><td>Date: <hr /></td></tr></tpl></table></div><p class="footer">$Rev: 181 $| $Date: 2010-06-04 18:59:21 +1000 (Fri, 04 Jun 2010) $</p></div><!-- END home-assignment-auth-couple -->');
	//HAAC

	
	//register events
	this.addEvents(
			/**
	         * @event loadsuccess
	         * Fires when a form's load ajax request is a success.
			 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
			 * @param {Ext.form.BasicForm}	form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
			 * @param {Ext.form.Action}		action 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	         */
			'loadsuccess',
			
			/**
	         * @event loadfailure
	         * Fires when a form's load ajax request is a failure.
			 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
			 * @param {Ext.form.BasicForm}	form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
			 * @param {Ext.form.Action}		action 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	         */
			'loadfailure',
			
			/**
	         * @event submitsuccess
	         * Fires when a form's submit ajax request is a success.
			 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
			 * @param {Ext.form.BasicForm} 	form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
			 * @param {Ext.form.Action}		action 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	         */
			'submitsuccess',
			
			/**
	         * @event submitfailure
	         * Fires when a form's submit ajax request is a failure.
			 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
			 * @param {Ext.form.BasicForm}	form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
			 * @param {Ext.form.Action}		action 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	         */
			'submitfailure'
	);
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
			id: this.id,
			frame:true,
			title: this.title,
			bodyStyle: 'padding:0'
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.PrintForm.superclass.constructor.call(this, config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.PrintForm, Ext.Panel, {
	/**
	 * Returns the current session that is being modified.
	 * @returns {number}			A number that is the id of the user's session.
	 */
	getSession: function() {return this.session;},
	
	/**
	 * Sets the id of the current session that is being modified.
	 * @param {number}	session		The number representing the user's session.
	 */
	setSession: function(session) {this.session = session;},
	/**
	 * Returns whether the user has a spouse or not.
	 * @returns {boolean}			A boolean that tell you if the user has a spouse.
	 */
	hasSpouse: function() {return this.spouse;},
	
	/**
	 * Lets this form know if the user has a spouse or not.
	 * @param {boolean}	spouse		A boolean that tell you if the user has a spouse.
	 */
	setSpouse: function(spouse) {this.spouse = spouse;},
	
	/**
	 * Returns whether the user serves overseas or not.
	 * @returns {boolean}			A boolean that tell you if the user serves overseas.
	 */
	isOverseas: function() {return this.overseas;},
	
	/**
	 * Lets this form know if the user serves overseas or not.
	 * @param {boolean}	overseas	A boolean that tell you if the user serves overseas.
	 */
	setOverseas: function(overseas) {this.overseas = overseas;},
	
	/**
	 * Takes the response object and decodes all the json strings into javascript objects. The usefull javascript objects are put into this.values.
	 * The objects will be stored in this.values['aussie-based'], this.values['home-assignment'] or this.values['international-assignment']
	 * based on which set of data has been passed to it.
	 * @param {Object}	response	An Object/Associative Array of json strings that contains all the reponses from form submittions.
	 * 								This holds the data that is to be displayed using templates on this form.
	 */
	processResponse: function(response) {
		var returnObj = {};
		
		//decode the response strings into json objects
		for (id in response) {
			form = Ext.getCmp(id);
			if (form.aussie_form == true && form.overseas_form == false) {
				returnObj = Ext.util.JSON.decode(response[id]);
				this.values['aussie-based'] = returnObj['tmn_data'];
			} else if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == true) {
				returnObj = Ext.util.JSON.decode(response[id]);
				this.values['home-assignment'] = returnObj['tmn_data'];
			} else if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == false) {
				returnObj = Ext.util.JSON.decode(response[id]);
				this.values['international-assignment'] = returnObj['tmn_data'];
			}
		}
	},
	
	/**
	 * Loads the Panel with the users TMN data
	 * @param {Object}	response	An Object/Associative Array of strings that contains all the reponses from form submittions.
	 * 								This holds the data that is to be displayed using templates on this form.
	 */
	loadForm: function(response) {
		
		//fill this.values with the object decribed by the json strings in response
		this.processResponse(response);
		
		//make sure there are values to display (if the values are missing then show an error message)
		if (this.values['aussie-based'] !== undefined || (this.values['international-assignment'] !== undefined && this.values['home-assignment'] !== undefined)) {
			//display the appropriate template based on the user's details
			if (this.isOverseas()) {
				if (this.hasSpouse()){
					this.templates['international-assignment']['spouse'].overwrite(this.body, this.values['international-assignment']);
					this.templates['home-assignment']['spouse'].append(this.body, this.values['home-assignment']);
					this.templates['international-assignment-auth']['spouse'].append(this.body, this.values['international-assignment']);
					this.templates['home-assignment-auth']['spouse'].append(this.body, this.values['home-assignment']);
				} else {
					this.templates['international-assignment']['single'].overwrite(this.body, this.values['international-assignment']);
					this.templates['home-assignment']['single'].append(this.body, this.values['home-assignment']);
					this.templates['international-assignment-auth']['single'].append(this.body, this.values['international-assignment']);
					this.templates['home-assignment-auth']['single'].append(this.body, this.values['home-assignment']);
				}
				
				//save useful sets of data for later submittion
				this.response['international-assignment'] = this.values['international-assignment'];
				this.response['home-assignment'] = this.values['home-assignment'];
			} else {
				if (this.hasSpouse()){
					this.templates['aussie-based']['spouse'].overwrite(this.body, this.values['aussie-based']);
				} else {
					this.templates['aussie-based']['single'].overwrite(this.body, this.values['aussie-based']);
				}
	
				//save useful sets of data for later submittion
				this.response['aussie-based'] = this.values['aussie-based'];
			}
			
			//tell the user how to save and print the TMN
			Ext.MessageBox.show({
				icon: Ext.MessageBox.WARNING,
				buttons: Ext.MessageBox.OK,
				closable: false,
				title: 'Warning',
				msg: 'To save your TMN press the \'Print\' button in the bottom right corner.'
			});
		} else {
			//tell the user there is an error with displaying their values
			Ext.MessageBox.show({
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.MessageBox.OK,
				closable: false,
				title: 'Error',
				msg: 'Your values can not be displayed. Try again by pressing the Previous button in the bottom left corner of the document followed by pressing the Next button in the bottom right. If you see this error again contact <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>.'
			});
		}
	},
	
	/**
	 * This handler deals with the success of the load request for this panel.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that succeeded (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadSuccess: function (form, action) {
		
	},
	
	/**
	 * Will display error messages based on the error that caused the failure. (It also runs the success code because this handler is often falsely triggered)
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadFailure: function (form, action){
		
	},
	
	/**
	 * Prints the users TMN then send the data to the backend to be stored in the Database
	 */
	submitForm: function() {
		//print it
		if (Ext.isChrome)
			window.print();
		else
			Ext.ux.Printer.print(this);
			
		//save the submitted json object
		Ext.Ajax.request({
			url: this.submit_url,
			scope: this,
			params: {
				session: this.session,
				json: Ext.util.JSON.encode(this.response)
			},
			callback: this.onSubmitSuccess
		});
	},
	
	/**
	 * Thanks the user for submitting their TMN.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitSuccess: function (form, action) {
		Ext.MessageBox.show({
			buttons: Ext.MessageBox.OK,
			closable: false,
			title: 'Thank You',
			msg: 'Thank You for doing your TMN. Your TMN has been saved for archiving, we will be able to pull this data up in the future but if you want access to these values in the short term please print a second copy for your own records.<br />Please now follow the instruction at the top of the page to submit your TMN.'
		});
	},
	
	/**
	 * This handler deals with the failure of the submit request for this panel.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitFailure: function (form, action){

	}
});
