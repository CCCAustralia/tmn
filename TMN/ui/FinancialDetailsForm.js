
Ext.ns('tmn', 'tmn.view');		//the namespace of the project

tmn.view.LastTMN = new Ext.Window({title: 'TMN - 2009', closable: false, width:418, height:436, resizable: false}); //a window for displaying the last saved TMN of 2009

/**
 * @class		tmn.view.FinancialDetailsForm
 * 
 * <p>
 * <b>Description:</b> The Form that collects a missionaries Financial Details.<br />
 * It sends Ajax requests to cookie_monster.php to update the calculated values on the page.
 * No calculations are done in this class. They are all done in cookie_monster.php.
 * </p>
 * 
 * @author		Michael Harrison	(<a href="mailto:michael.harrison@ccca.org.au">michael.harrison@ccca.org.au</a>)
 * 				& Thomas Flynn		(<a href="mailto:tom.flynn@ccca.org.au">tom.flynn@ccca.org.au</a>)
 * 
 * @namespace 	tmn.view
 * @extends		Ext.form.FormPanel
 * @version		TMN 2.1.0
 * @note		The TMN uses the MVC design structure, read up on it at <a href="http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller">http://en.wikipedia.org/wiki/Model-view-controller</a>).
 * @demo		http://mportal.ccca.org.au/TMN
 */
tmn.view.FinancialDetailsForm = function(view, config) {
	/**
	 * @cfg {Object}	view			The object that defines the container that holds this form
	 * @note To be able to use this property you must pass it to the constructor when you create an instance of this class.
	 */
	this.view = view || {};					//the view that this form is contained in
	//set config variable to passed or default
	config = config || {};

	/**
	 * Tells you if the form is saved or has unsaved data on it
	 * @type {bool}
	 */
	this.saved				=	true;
	/**
	 * Tells you if the form can be saved and deleted by the user or if the user is locked out from those features
	 * (locking happens when the user has submitted the session)
	 * @type {bool}
	 */
	this.locked				=	false;
	/**
	 * Tells you if the form is allowing data to be sent to the back end for processing
	 * @type {bool}
	 */
	this.processingAllowed	=	true;
	
	//set config options to passed or default
	/**
	 * @cfg {String}	id				The id parameter of the html tag that contains the form.<br />
	 * 									Default: 'financial_details_form'
	 */
	this.id					=	config.id || 'financial_details_form';
	/**
	 * @cfg {String}	title			The title displayed in the header of the form.<br />
	 * 									Default: 'Financial Details'
	 */
	this.title				=	config.title || 'Financial Details';
	/**
	 * @cfg {String}	crud_url		The url of the server script that will process the form's crud (Create, Retrieve, Update, Delete) requests.<br />
	 * 									Default: 'php/imp/session_crud.php'
	 */
	this.crud_url			=	config.crud_url || 'php/imp/session_crud.php';
	/**
	 * @cfg {String}	load_url		The url of the server script that will process the forms load request.<br />
	 * 									Default: 'php/load_financial_details.php'
	 */
	this.load_url			=	config.load_url || 'php/load_financial_details.php';
	/**
	 * @cfg {String}	submit_url		The url of the server script that will process the forms submit request.<br />
	 * 									Default: 'php/submit_financial_details.php'
	 */
	this.submit_url			=	config.submit_url || 'php/submit_financial_details.php';
	/**
	 * @cfg {number}	submit_url		The number of milliseconds the form will wait after the user stops typing before it will send the ajax request to the backend.<br />
	 * 									Default: 1000
	 */
	this.keyup_timeout		=	config.keyup_timeout || 1000;
	/**
	 * @cfg {String}	home_assignment	If the form is an overseas form, then this lets you know if it is for when the overseas missionary is on home assignment or not.
	 * 									If a user is not an Aussie Based Missionary they will not be able to view this form<br />
	 * 									Default: false
	 */
	this.home_assignment;
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
	(config.home_assignment === undefined) ? this.home_assignment = false : this.home_assignment = config.home_assignment;
	(config.aussie_form === undefined) ? this.aussie_form = true : this.aussie_form = config.aussie_form;
	(config.overseas_form === undefined) ? this.overseas_form = false : this.overseas_form = config.overseas_form;
	
	//class wide variables definitions
	/**
	 * This is the object/associative array of financial data for each form.
	 * When a form writes to this variable the value is put into financial_data[<insert the form's id>][<insert the field's name>].
	 * @type Object/Associative Array
	 */
	this.financial_data = {};
	
	//register events
	this.addEvents(
		
		/**
         * @event financialdataupdated
         * Fires when a fields value is changed. If you are looking for where this event is fired it is at the bottom of the constructor.
		 * @param {Ext.form.FormPanel}	this 				A reference to the form that called it (ie send it this)
		 * @param {Ext.form.Field}		field 				A reference to the field that was updated
		 * @param {Mixed}				newValue 			The new value of the field
		 * @param {Boolean}				send_ajax_request 	Whether you want the data set to be sent to the server for processing after update or not
         */
		'financialdataupdated',
		
		/**
         * @event resetfinancialdata
         * Fires when a session is loaded or deleted.
		 * @param {Ext.form.FormPanel}	this 				A reference to the form that called it (ie send it this)
         */
		'resetfinancialdata',
		
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
		'submitfailure',
		
		/**
         * @event loadsession
         * Fires when a session is selected.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'loadsession',
		
		/**
         * @event loadsessionsuccess
         * Fires when the server successfully returns from retrieving a session after loadsession has been fired.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
		 * @param {Object} 				response: 	The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 											http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 	The parameter to the request call.
         */
		'loadsessionsuccess',
		
		/**
         * @event savesession
         * Fires when the user clicks save when a session is selected.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'savesession',
		
		/**
         * @event savesession
         * Fires when the user clicks save when a session is selected.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'saveassession',
		
		/**
         * @event saveassessionsuccess
         * Fires when the server successfully returns from creating a session after saveassession has been fired.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
		 * @param {Object} 				response: 	The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 											http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 	The parameter to the request call.
         */
		'saveassessionsuccess',
		
		/**
         * @event deletesession
         * Fires when the user clicks delete when a session is selected.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'deletesession',
		
		/**
         * @event deletesessionsuccess
         * Fires when the server successfully returns from deleteing a session after deletesession has been fired.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
		 * @param {Object} 				response: 	The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 											http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 	The parameter to the request call.
         */
		'deletesessionsuccess',
		
		/**
         * @event resetsession
         * Fires when the user clicks reset on the form. It will clear all data associated with a session (whether its in the database or not) from the form.
		 * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'resetsession'
	);
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.<br />
	 * Note: You will see that fields don't have a 'change' listener but they update on change. This is done at the bottom of the constructor.
	 */
	this.config =  {
		
		id:		this.id,
		frame:	true,
		title:	this.title,
		tbar:	new Ext.ux.StatusBar({
			defaultText: 'Ready',
			plugins: new Ext.ux.ValidationStatus({form: this.id}),
			statusAlign: 'right',
			items: [
				    ' ',' ','Session:         ', ' ',
				    new Ext.ux.IconCombo({
				    	itemId: 'session_combo',
			       		width: 200,
					    fieldLabel: 'Session',
					    valueField: 'SESSION_ID',
					    hiddenName: 'SESSION_COMBO',
					    hiddenId: 'SESSION_COMBO',
					    displayField: 'SESSION_NAME',
		                lockedField: 'LOCKED',
					    emptyText:'Select a Session or start typing...',
					    triggerAction: 'all',
			        	editable: false,
					    forceSelection: true,
					    allowBlank:true,
					    
					    mode: 'local',
					    // store getting items from server
					    store: new Ext.data.JsonStore({
					        itemId:		'session_store',
					        root:		'Tmn_Sessions',
					        storeId:	'SESSION_ID',
					        fields:		['SESSION_ID', 'SESSION_NAME', 'LOCKED'],
					        url:		'php/imp/combofill.php',
					        baseParams:	{mode: 'Tmn_Sessions', aussie_form:this.aussie_form, home_assignment:this.home_assignment, overseas_form:this.overseas_form},
					        autoLoad:	true
					    }),
					    listeners:		{
					    	scope: this,
					    	select: function(combo, record, index) {
					    		this.fireEvent('loadsession', this);
					    	}
					    }
				    }), ' ', '-', ' ',
				    {
				    	itemId: 'save_session_button',
						text: 'Save',
						width: 80,
						scope: this,
						handler: function(){
							this.fireEvent('savesession', this);
						}
				    }, ' ', '-', ' ',
				    {
				    	itemId: 'saveas_session_button',
						text: 'Save As',
						width: 80,
						scope: this,
						handler: function(){
							this.fireEvent('saveassession', this);
						}
				    }, ' ', '-', ' ',
				    {
				    	itemId: 'delete_session_button',
						text: 'Delete',
						width: 80,
						scope: this,
						handler: function(){
							this.fireEvent('deletesession', this);
						}
				    }, ' ', '-', ' ',
				    {
				    	itemId: 'reset_session_button',
						text: 'Reset',
						width: 80,
						scope: this,
						handler: function(){
							this.fireEvent('resetsession', this);
						}
				    }, ' ', '-'
				]
		}),
		
		items:	[
		       
		///////////////////////////////Assignment Dates Panel//////////////////////////////////////

			{
				itemId: 'os_assignment_panel',
				layout: 'form',
				title: 'Dates of ' + this.title,
				labelWidth: 150,
				bodyStyle: 'padding:10px',
				defaultType: 'datefield',
				items: [
					{
						itemId: 'os_assignment_start_date',
						fieldLabel: 'Start Date',
						name: 'OS_ASSIGNMENT_START_DATE',
						format: 'd-m-Y',
						allowBlank: false,
						editable: false,
						vtype: 'daterange',
						endDateField: function(){return this.getForm().items.map['os_assignment_end_date'];}.createDelegate(this), // id of the end date field
						listeners: {
							scope: this,
							select: function (field, date) {
								this.fireEvent('financialdataupdated', this, field, date.format('d-m-Y'), false);
							}
						}
					},
					{
						itemId: 'os_assignment_end_date',
						fieldLabel: 'End Date',
						name: 'OS_ASSIGNMENT_END_DATE',
						format: 'd-m-Y',
						allowBlank: false,
						editable: false,
						vtype: 'daterange',
						startDateField: function(){return this.getForm().items.map['os_assignment_start_date'];}.createDelegate(this), // id of the end date field
						listeners: {
							scope: this,
							select: function (field, date) {
								this.fireEvent('financialdataupdated', this, field, date.format('d-m-Y'), false);
							}
						}
					}
				]
			},
		
		///////////////////////////////Taxable Income//////////////////////////////////////
		
			{
				itemId: 'taxable_income_panel',
				layout: 'column',
				//title: 'Taxable Income',
				buttonAlign: 'right',
				defaults: {
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						allowBlank: false,
						minValue: 0
					}
				},
				items: [
					{
						itemId: 'my',
						columnWidth: 0.5,
						layout: 'form',
						title: 'My Taxable Income',
						items: [
							{
								itemId: 'stipend',
								name: 'STIPEND',
								fieldLabel: 'Stipend',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the estimated amount that will go into your bank account each month.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
										});
									}
								}
							},
							{
								itemId: 'hs',
								xtype: 'panel',
								layout: 'form',
								defaultType: 'numberfield',
								defaults:{
									allowBlank: false,
									minValue: 0
								},
								items: [
									{
										itemId: 'housing_stipend',
										name: 'HOUSING_STIPEND',
										readOnly: true,
										cls: 'x-form-readonly-red',
										fieldLabel: 'Housing Stipend',
										listeners: {
											focus: function(field)	{field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'This is the extra amount that needs to be added to your Stipend to cover your housing.<br />This happens when your MFBs don\'t fully cover your housing requirements.'
												});
											}
										}
									},
									{
										itemId: 'first_home_saver_account',
										name: 'FIRST_HOME_SAVER_ACCOUNT',
										readOnly: true,
										cls: 'x-form-readonly-red',
										fieldLabel: 'First Home Saver Account',
										listeners: {
											focus: function(field)	{field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'This is the 9% of your stipend that goes toward your First Home Saver Account. If you would like to put more toward your First Home Saver Account please just increase your Stipend.'
												});
											}
										}
									},
									{
										itemId: 'net_stipend',
										name: 'NET_STIPEND',
										readOnly: true,
										cls: 'x-form-readonly-red',
										fieldLabel: 'Net Stipend',
										listeners: {
											focus: function(field)	{field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'This is the estimated amount that will go into your bank account each month plus your housing stipend.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
												});
											}
										}
									}
								]
							},
							{
								itemId: 'post_tax_super',
								name: 'POST_TAX_SUPER',
								fieldLabel: 'Post Tax Super',
								value: 0,
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Record the amount of post-tax voluntary superannuation contribution you would like to be paid.<br />To ensure you are eligible for the superannuation co-contribution scheme, go to <a href="http://www.ato.gov.au/individuals/content.asp?doc=/content/42616.htm&page=1&H1" target="_blank">the ATO website</a>.<br />You may be eligible to receive up to $84/month.'
										});
									}
								}
							},
							{
								itemId: 'additional_tax',
								name: 'ADDITIONAL_TAX',
								fieldLabel: 'Additional Tax',
								value: 0,
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Most people will leave this field blank. CCCA will deduct the appropriate amount of tax.<br />Only record Additional Tax if you want <b>extra</b> tax deducted each month (e.g. to cover investment income).'
										});
									}
								}
							},
							{
								itemId: 'tax',
								name: 'TAX',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Tax',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the amount of tax that is required for the specified stipend.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							},
							{
								itemId: 'taxable_income',
								name: 'TAXABLE_INCOME',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Taxable Income',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is your taxable income consisting of the sum of the above fields, it is used for tax purposes.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							},
							{
								xtype: 'button',
								text: 'Show/Hide Your Last Saved TMN of 2009',
								enableToggle: true,
								scope: this,
								toggleHandler: function (button, state){
									if (state == true){
										tmn.view.LastTMN.html = '<iframe src=\"http://mportal.ccca.org.au/TMN/php/tmn_2009.php\" height=400px width=400px></iframe>';
										tmn.view.LastTMN.show();
									} else {
										tmn.view.LastTMN.hide();
									}
								},
								tooltip: 'This is NOT necessarily the TMN you submitted in 2009!'
							}
						]
					},
					{
						itemId: 'spouse',
						columnWidth: 0.5,
						layout: 'form',
						title: 'Spouse Taxable Income',
						items: [
							{
								itemId: 's_stipend',
								name: 'S_STIPEND',
								fieldLabel: 'Stipend',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the estimated amount that will go into your bank account each month.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
										});
									}
								}
							},
							{
								itemId: 'hs',
								xtype: 'panel',
								layout: 'form',
								defaultType: 'numberfield',
								defaults:{
									allowBlank: false,
									minValue: 0
								},
								items: [
									{
										itemId: 's_housing_stipend',
										name: 'S_HOUSING_STIPEND',
										readOnly: true,
										cls: 'x-form-readonly-red',
										fieldLabel: 'Housing Stipend',
										listeners: {
											focus: function(field)	{field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'This is the extra amount that needs to be added to your Stipend to cover your housing.<br />This happens when your MFBs don\'t fully cover your housing requirements.'
												});
											}
										}
									},
									{
										itemId: 's_net_stipend',
										name: 'S_NET_STIPEND',
										readOnly: true,
										cls: 'x-form-readonly-red',
										fieldLabel: 'Net Stipend',
										listeners: {
											focus: function(field)	{field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'This is the estimated amount that will go into your bank account each month plus your housing stipend.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
												});
											}
										}
									}
								]
							},
							{
								itemId: 's_post_tax_super',
								name: 'S_POST_TAX_SUPER',
								fieldLabel: 'Post Tax Super',
								value: 0,
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Record the amount of post-tax voluntary superannuation contribution you would like to be paid.<br />To ensure you are eligible for the superannuation co-contribution scheme, go to <a href="http://www.ato.gov.au/individuals/content.asp?doc=/content/42616.htm&page=1&H1" target="_blank">the ATO website</a>.<br />You may be eligible to receive up to $84/month.'
										});
									}
								}
							},
							{
								itemId: 's_additional_tax',
								name: 'S_ADDITIONAL_TAX',
								fieldLabel: 'Additional Tax',
								value: 0,
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Most people will leave this field blank. CCCA will deduct the appropriate amount of tax.<br />Only record Additional Tax if you want <b>extra</b> tax deducted each month (e.g. to cover investment income).'
										});
									}
								}
							},
							{
								itemId: 's_tax',
								name: 'S_TAX',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Tax',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the amount of tax that is required for the specified stipend.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							},
							{
								itemId: 's_taxable_income',
								name: 'S_TAXABLE_INCOME',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Taxable Income',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is your taxable income consisting of the sum of the above fields, it is used for tax purposes.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							}
						]
					}, //eo spouse financial details
					
					{
						itemId: 'os_residency',
						columnWidth: 1,
						layout: 'form',
						header: false,
						labelWidth: 350,
						items: [
							{
								itemId: 'os_resident_for_tax_purposes',
					       		xtype: 'combo',
					       		width: 150,
					       		fieldLabel: 'Are you an Australian Resident for Tax Purposes?',
					       		name: 'OS_RESIDENT_FOR_TAX_PURPOSES',
					        	hiddenName: 'OS_RESIDENT_FOR_TAX_PURPOSES',
					        	hiddenId: 'OS_RESIDENT_FOR_TAX_PURPOSES_hidden',
					       		triggerAction:'all',
					       		emptyText: 'Enter Yes or No...',
					       		validationEvent: 'blur',
					       		allowBlank: false,
					        	editable:false,
					            mode:'local',
					            value: 1,
					            
					            store:new Ext.data.SimpleStore({
					                 fields:['residentCode', 'residentName'],
					                 data:[[0,'No'],[1,'Yes']]
					            }),
					            displayField:'residentName',
					            valueField:'residentCode',
					            
					            listeners: {
									scope: this,
				                	select: function(combo, record, index) {
										this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
				                	}
					            }
							}
						]
					}
				]
			}, //eo taxable_income_panel
			
		///////////////////////////////Housing//////////////////////////////////////
			
			{
				itemId: 'housing_panel',
				layout: 'form',
				title: 'Housing',
				//collapsed: true,
				defaultType: 'numberfield',
				bodyStyle: 'padding:10px',
				labelWidth: 300,
				defaults: {
					allowBlank: false,
					minValue: 0,
					value: 0
				},
				
				items: [
					{
						itemId: 'housing',
						name: 'HOUSING',
						fieldLabel: 'Housing',
						listeners: {
							scope: this,
							render: function(c) {
								Ext.QuickTips.register({
									target: c.getEl(),
									text: 'Record the amount you would like to be paid from your Support Account each fortnight/month for housing.<br />If this amount can not be covered by your MFBs, your stipend will be increased. We call this amount, Housing Stipend. This is taxable income that will not go into your account but will be paid directly toward your housing.'
								});
							}
						}
					},
					{
						itemId: 'am',
						xtype: 'panel',
						layout: 'form',
						defaultType: 'numberfield',
						defaults:{
							allowBlank: false,
							minValue: 0
						},
						items: [
							{
								itemId: 'additional_mortgage',
								name: 'ADDITIONAL_MORTGAGE',
								readOnly: true,
								cls: 'x-form-readonly-red',
								fieldLabel: 'Additional Mortgage Payment',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the Future Investment (caluclated as 9% of your stipend) that goes toward your mortgage.'
										});
									}
								}
							},
							{
								itemId: 'total_housing',
								name: 'TOTAL_HOUSING',
								readOnly: true,
								cls: 'x-form-readonly-red',
								fieldLabel: 'Total Housing Payment',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the estimated total amount that will go toward your housing.'
										});
									}
								}
							}
						]
					},
					{
						itemId: 'additional_housing',
						name: 'ADDITIONAL_HOUSING',
						readOnly: true,
						cls: 'x-form-readonly',
						fieldLabel: 'Monthly Additional Housing Allowance',
						listeners: {
							focus: function(field)	{field.blur();},
							render: function(c) {
								Ext.QuickTips.register({
									target: c.getEl(),
									text: 'Housing payments above the Maximum Housing MFB (set each year based on national median housing price) are not part of your Ministry Fringe Benifits.<br /><b><i>This will be automatically calculated</i></b>.'
								});
							}
						}
					},
					{
		            	itemId: 'housing_frequency',
		           		xtype: 'combo',
		           		fieldLabel: 'How often would you like to be paid your housing allowance?',
		           		name: 'HOUSING_FREQUENCY',
		            	hiddenName: 'HOUSING_FREQUENCY',
		            	hiddenId: 'HOUSING_FREQUENCY_hidden',
		           		triggerAction:'all',
		           		emptyText: 'Choose a frequency...',
		           		validationEvent: 'blur',
		            	editable:false,
		                mode:'local',
		                hiddenValue: 0,
		                
		                store:new Ext.data.SimpleStore({
		                     fields:['housingfrequencyCode', 'housingfrequencyText'],
		                     data:[[0,'Monthly'],[1,'Fortnightly']]
		                }),
		                displayField:'housingfrequencyText',
		                valueField:'housingfrequencyCode',
		                
		                listeners: {
							scope: this,
		                	select: function(combo, record, index) {
								this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
		                	}
		                }
					}
				]
			}, //eo housing
			
		///////////////////////////////Overseas Housing Allowance//////////////////////////////////////
				
			{
				itemId: 'os_overseas_housing_panel',
				layout: 'form',
				title: 'Overseas Housing Allowance',
				//collapsed: true,
				defaultType: 'numberfield',
				bodyStyle: 'padding:10px',
				defaults:{
					width: 140,
					minValue: 0,
					value: 0
				},
				
				items: [
					{
						itemId: 'os_overseas_housing',
						name: 'OS_OVERSEAS_HOUSING',
						fieldLabel: 'Overseas Housing Allowance',
						listeners: {
							scope: this,
							render: function(c) {
								Ext.QuickTips.register({
									target: c.getEl(),
									text: 'This is the amount of money needed to maintain the house you have in the country you minister in.'
								});
							}
						}
					}
				]
			}, //eo overseas housing allowance
		
		///////////////////////////////Living Away From Home Allowance//////////////////////////////////////
			
			{
				itemId: 'os_lafha_panel',
				layout: 'column',
				//title: 'MFB',
				//collapsed: true,
				defaults: {
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						width: 140,
						allowBlank: false,
						minValue: 0
					}
				},
				items: [
					{
						itemId: 'my',
						layout: 'form',
						columnWidth: 0.5,
						title: 'My LAFHA',
						items: [
							{
								itemId: 'os_lafha',
								name: 'OS_LAFHA',
								fieldLabel: 'LAFHA',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Living Away From Home Allowance. This is to assist you will on assignment outside of Australia. While this allowance has no limit, it is part of your Financial Package and your Financial Package does.'
										});
									}
								}
							}
						]
					}, //eo my lafha
					{
						itemId: 'spouse',
						layout: 'form',
						columnWidth: 0.5,
						title: 'Spouse LAFHA',
						items: [
							{
								itemId: 's_os_lafha',
								name: 'S_OS_LAFHA',
								fieldLabel: 'LAFHA',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Living Away From Home Allowance. This is to assist you will on assignment outside of Australia. While this allowance has no limit, it is part of your Financial Package and your Financial Package does.'
										});
									}
								}
							}
						]
					} //eo spouse lafha
				]
			}, //eo os_lafha_panel
			
		///////////////////////////////MFB//////////////////////////////////////
			
			{
				itemId: 'mfb_panel',
				layout: 'column',
				//title: 'MFB',
				//collapsed: true,
				defaults: {
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						width: 140,
						allowBlank: false,
						minValue: 0
					}
				},
				items: [
					{
						itemId: 'my',
						layout: 'form',
						columnWidth: 0.5,
						title: 'My MFB',
						items: [
							{
				            	itemId: 'mfb_rate',
				           		xtype: 'combo',
				           		fieldLabel: 'MFB Rate',
				           		name: 'MFB_RATE',
				            	hiddenName: 'MFB_RATE',
				            	hiddenId: 'MFB_RATE_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Rate...',
				           		validationEvent: 'blur',
				           		allowBlank: false,
				            	editable:false,
				                mode:'local',
				                hiddenValue: 2,
				                value: 2,
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['mfbRateCode', 'mfbRateText'],
				                     data:[[0,'Zero MFBs'],[1,'Half MFBs'],[2,'Full MFBs']]
				                }),
				                displayField:'mfbRateText',
				                valueField:'mfbRateCode',
						                
				                listeners: {
									scope: this,
				                	select: function(combo, record, index) {
										this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Please select the MFB rate allowed for your role at CCCA.'
										});
									}
				                }
				            },
							{
								itemId: 'max_mfb',
								name: 'MFB',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'MFB',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is your Ministry Fringe Benefits. It will be automatically set to the maximum that you are allowed.<br />If you would like to claim more MFB, increase your Taxable Income.'
										});
									}
								}
							},
							{
								itemId: 'claimable_mfb',
								name: 'CLAIMABLE_MFB',
								readOnly: true,
								cls: 'x-form-readonly-red',
								fieldLabel: 'Claimable MFBs',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the amount of your Ministry Fringe Benefits that you can make claims from once your housing has been deducted.<br />It will be automatically set to the maximum that you are allowed.<br />If you would like to claim more MFB, increase your Stipend.'
										});
									}
								}
							}
						]
					}, //eo my mfb
					{
						itemId: 'spouse',
						layout: 'form',
						columnWidth: 0.5,
						title: 'Spouse MFB',
						items: [
							{
				            	itemId: 's_mfb_rate',
				           		xtype: 'combo',
				           		fieldLabel: 'MFB Rate',
				           		name: 'S_MFB_RATE',
				            	hiddenName: 'S_MFB_RATE',
				            	hiddenId: 'S_MFB_RATE_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Rate...',
				           		validationEvent: 'blur',
				           		allowBlank: false,
				            	editable:false,
				                mode:'local',
				                hiddenValue: 2,
				                value: 2,
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['mfbRateCode', 'mfbRateText'],
				                     data:[[0,'Zero MFBs'],[1,'Half MFBs'],[2,'Full MFBs']]
				                }),
				                displayField:'mfbRateText',
				                valueField:'mfbRateCode',
						                
				                listeners: {
									scope: this,
				                	select: function(combo, record, index) {
										this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Please select the MFB rate allowed for your role at CCCA.'
										});
									}
				                }
				            },
							{
								itemId: 's_max_mfb',
								name: 'S_MFB',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'MFB',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is your Ministry Fringe Benefits. It will be automatically set to the maximum that you are allowed.<br />If you would like to claim more MFB, increase your Taxable Income.'
										});
									}
								}
							},
							{
								itemId: 's_claimable_mfb',
								name: 'S_CLAIMABLE_MFB',
								readOnly: true,
								cls: 'x-form-readonly-red',
								fieldLabel: 'Claimable MFBs',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the amount of your Ministry Fringe Benefits that you can make claims from once your housing has been deducted.<br />It will be automatically set to the maximum that you are allowed.<br />If you would like to claim more MFB, increase your Taxable Income.'
										});
									}
								}
							}
						]
					} //eo spouse mfb
				]
			}, //eo mfb_panel
			
			///////////////////////////////Super//////////////////////////////////////

			{
				itemId: 'super_panel',
				layout: 'column',
				//title: 'MFB',
				//collapsed: true,
				defaults: {
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						allowBlank: false,
						minValue: 0
					}
				},
				items: [
					{
						itemId: 'my',
						layout: 'form',
						title: 'My Super Details',
						columnWidth: 0.5,
						
						items: [
						    {
						    	width: 140,
				            	itemId: 'retirement_investment_mode',
				           		xtype: 'combo',
				           		fieldLabel: 'Where do you want your Retirement Investment to go?',
				           		name: 'RETIREMENT_INVESTMENT_MODE',
				            	hiddenName: 'RETIREMENT_INVESTMENT_MODE',
				            	hiddenId: 'RETIREMENT_INVESTMENT_MODE_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Pre-tax super...',
				           		validationEvent: 'blur',
				           		allowBlank: false,
				            	editable:false,
				                mode:'local',
						        hiddenValue: 0,
						        value: 'Pre-Tax Super',
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['retirementInvestmentCode', 'retirementInvestmentName'],
				                     data:[[0,'Pre-Tax Super'],[1,'Mortgage'],[2,'First Home Saver Account'],[3,'Other Investment']]
				                }),
				                displayField:'retirementInvestmentName',
				                valueField:'retirementInvestmentCode',
				                
				                listeners: {
									scope: this,
				                	//when index 1, "No", is selected show life cover amount
				                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
				                	select: function(combo, record, index) {
				                		//actions for Pre Tax Super
										if (index == 0) {
											//undo other options
											//hide and set to zero Mortgage
											this.getComponent('housing_panel').getComponent('am').hide();
											//hide and set to zero First Home Saver Account
											this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').hide();
											//hide and set to zero Other Investment
											
											//apply changes for pre tax super
											combo.nextSibling().expand();
										
										//actions for Mortgage
										} else if (index == 1) {
											//undo other options
											//hide and set to zero Pre-Tax Super
											combo.nextSibling().collapse();
											//hide and set to zero First Home Saver Account
											this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').hide();
											//hide and set to zero Other Investment
											
											//apply changes for Mortgage
											//combo.nextSibling().collapse();
											
										//actions for First Home Saver Account
										} else if (index == 2) {
											//undo other options
											//hide and set to zero Pre-Tax Super
											//hide and set to zero Mortgage
											//hide and set to zero Other Investment
											
											//apply changes for First Home Saver Account
											//combo.nextSibling().collapse();
											
										//actions for Other Investments
										} else if (index == 3) {
											//undo other options
											//hide and set to zero Pre-Tax Super
											//hide and set to zero Mortgage
											//hide and set to zero First Home Saver
											
											//apply changes for Other Investment
											//combo.nextSibling().collapse();
										}
										
										this.fireEvent('financialdataupdated', this, combo, index, false);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: '9% of your Taxable Income goes toward your Super Fund. This is a compulsary amount enforced by the government. As missionaries we also get MFBs. The government does not require a compulsary percentage of your MFBs to go toward your Super Fund. However, to look after you CCCA requires you, as a minimum, to set aside another 9% of your stipend (for someone on Half-MFBs it would be 4.5% of your Stipend, for somone on Zero-MFBs it would be 0% of your Stipend) toward your future. This amount is called your Retirement Investment. You may add more than the minimum and you can select where it goes using this combo box.'
										});
									}
				                }
						    },
						    {
				            	itemId: 'pre_tax_super_panel',
				            	xtype: 'panel',
				            	layout: 'form',
				            	//collapsed: true,
				            	items: [
									{
										xtype:				'numberfield',
										itemId:				'pre_tax_super',
										name:				'PRE_TAX_SUPER',
										cls:				'x-form-readonly',
										value:				0,
										minValue:			0,
										allowBlank:			false,
										fieldLabel:			'Pre Tax Super',
										enableKeyEvents:	true,
										listeners: {
											focus: function(field){field.blur();},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: 'Record the amount of Pre-tax Super you would like to be paid from your Support Account each month.<br />This amount is not Taxed.'
												});
											}
										}
									},
									{
										xtype: 'button',
										itemId: 'pre_tax_super_mode',
										enableToggle: true,
										text: 'Manually Set Pre Tax Super',
										margins: {top:0, right:0, bottom:10, left:100},
										scope: this,
										toggleHandler: function(button, state){
											//Button has been pressed so they are in manual mode
											if(state == true){
												//removes readonly
												this.getForm().items.map['pre_tax_super'].purgeListeners();
												this.getForm().items.map['pre_tax_super'].removeClass('x-form-readonly');
												//update the mode to manual
												//(need to send it {getName: function(){return 's_pre_tax_super_mode';}} as the field because it is expecting an object
												//with the getName() method to tell it which value it needs to update)
												this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'manual', this.processingAllowed);
												//add update listener
												this.getForm().items.map['pre_tax_super'].enableKeyEvents = true;
												this.getForm().items.map['pre_tax_super'].addListener('keyup', function(field, event) {this.fireEvent('financialdataupdated', this, field, field.getValue(), this.processingAllowed);}, this, {buffer: this.keyup_timeout});
											} else {
												//stops it updating on change
												this.getForm().items.map['pre_tax_super'].purgeListeners();
												//makes it readonly
												this.getForm().items.map['pre_tax_super'].addClass('x-form-readonly');
												this.getForm().items.map['pre_tax_super'].enableKeyEvents = false;
												this.getForm().items.map['pre_tax_super'].addListener('focus', function(field){field.blur();});
												//update the mode to auto
												//(need to send it {getName: function(){return 's_pre_tax_super_mode';}} as the field because it is expecting an object
												//with the getName() method to tell it which value it needs to update)
												this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'auto', this.processingAllowed);
											}
										}
									}
								]
						    },
							{
								itemId: 'employer_super',
								name: 'EMPLOYER_SUPER',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Employer Super',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the required employer super contribution.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							},
							{
								width: 140,
				            	itemId: 'super_fund',
				           		xtype: 'combo',
				           		fieldLabel: 'Is your super fund IOOF?',
				           		name: 'SUPER_FUND',
				            	hiddenName: 'SUPER_FUND',
				            	hiddenId: 'SUPER_FUND_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Yes or No...',
				           		validationEvent: 'blur',
				           		allowBlank: false,
				            	editable:false,
				                mode:'local',
						        hiddenValue: 1,
						        value: 'Yes',
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['ioofCode', 'ioofName'],
				                     data:[[0,'No'],[1,'Yes']]
				                }),
				                displayField:'ioofName',
				                valueField:'ioofCode',
				                
				                listeners: {
									scope: this,
				                	//when index 1, "No", is selected show life cover amount
				                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
				                	select: function(combo, record, index) {
										if (index == 1) {
											combo.nextSibling().expand();
										} else {
											combo.nextSibling().collapse();
											Ext.Msg.alert('Super Fund Change!', 'If you are changing your Super Fund you need to fill out <a href="pdf/superfund_change.pdf" target="_blank">this</a> form for the change to apply.');
										}
										this.fireEvent('financialdataupdated', this, combo, index, false);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Select whether you have nominated IOOF for your super fund or not.<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a super nomination form.</b>'
										});
									}
				                }
				            },
				            {
				            	itemId: 'additional_life_cover_panel',
				            	xtype: 'panel',
				            	layout: 'form',
				            	//collapsed: true,
				            	defaults: {
				            		width: 140
				            	},
				            	items: [
				            		{
						            	itemId: 'additional_life_cover',
						           		xtype: 'combo',
						           		fieldLabel: 'Weekly Life Cover Amount',
						           		name: 'ADDITIONAL_LIFE_COVER',
						            	hiddenName: 'ADDITIONAL_LIFE_COVER',
						            	hiddenId: 'ADDITIONAL_LIFE_COVER_hidden',
						           		triggerAction:'all',
						           		emptyText: 'Enter Amount of Life Cover...',
						           		validationEvent: 'blur',
						            	editable:false,
						                mode:'local',
						                hiddenValue: 0,
						                value: '$1',
						                
						                store:new Ext.data.SimpleStore({
						                     fields:['lifecoverCode', 'lifecoverText'],
						                     data:[[0,'$1'],[1,'$2'],[2,'$3'],[3,'$4'],[4,'$5'],[5,'$6'],[6,'$7'],[7,'$8'],[8,'$9'],[9,'$10']]
						                }),
						                displayField:'lifecoverText',
						                valueField:'lifecoverCode',
						                
						                listeners: {
											scope: this,
						                	select: function(combo, record, index) {
						                		Ext.Msg.alert('Life Cover Change!', 'If you are changing your Life Cover you need to fill out <a href="pdf/ioof_lifecover_change.zip" target="_blank">this</a> form for the change to apply.');
												this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
						                	},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: '<b>Note:</b> This is a weekly figure.<br />CCCA will pay $1/week. If you would like additional Life Cover (on top of the $1/week) record the total amount (including the $1/week to a maximum of $10/week).<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a Life Cover Change form.</b>'
												});
											}
						                }
						            }
				            	]
				            },
				            {
				            	width: 140,
				            	itemId: 'income_protection_cover_source',
				           		xtype: 'combo',
				           		fieldLabel: 'Where should your Income Protection Cover be taken from?',
				           		name: 'INCOME_PROTECTION_COVER_SOURCE',
				            	hiddenName: 'INCOME_PROTECTION_COVER_SOURCE',
				            	hiddenId: 'INCOME_PROTECTION_COVER_SOURCE_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Source of the Income Protection Cover...',
				           		validationEvent: 'blur',
				            	editable:false,
				                mode:'local',
				                hiddenValue: 0,
				                value: 'Support Account',
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['lifecoversourceCode', 'lifecoversourceText'],
				                     data:[[0,'Support Account'],[1,'Super Fund']]
				                }),
				                displayField:'lifecoversourceText',
				                valueField:'lifecoversourceCode',
				                
				                listeners: {
									scope: this,
				                	select: function(combo, record, index) {
				                		Ext.Msg.alert('INCOME PROTECTION Cover Change!', '<b>You have changed where your INCOME PROTECTION Cover is taken from.<br /><br />Note:<br />This cover is <i>DIFFERENT</i> to your Life Cover.<br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!</b>');
										this.fireEvent('financialdataupdated', this, combo, index, false);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Nominate where your Income Protection Cover should be taken from.<br /><b>This cover is <i>DIFFERENT</i> to your Life Cover.</b><br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!'
										});
									}
				                }
				            }
							
						]
					}, //eo my super
					{
						itemId: 'spouse',
						layout: 'form',
						title: 'Spouse Super Details',
						columnWidth: 0.5,
						
						items: [
							{
								itemId:				's_pre_tax_super',
								name:				'S_PRE_TAX_SUPER',
								cls:				'x-form-readonly',
								fieldLabel:			'Pre Tax Super',
								value:				0,
								enableKeyEvents:	true,
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Record the amount of Pre-tax Super you would like to be paid from your Support Account each month.<br />This amount is not Taxed.'
										});
									}
								}
							},
							{
								xtype: 'button',
								itemId: 's_pre_tax_super_mode',
								enableToggle: true,
								text: 'Manually Set Pre Tax Super',
								margins: {top:0, right:0, bottom:3, left:0},
								scope: this,
								toggleHandler: function(button, state){
									//Button has been pressed so they are in manual mode
									if(state == true){
										//removes readonly
										this.getForm().items.map['s_pre_tax_super'].purgeListeners();
										this.getForm().items.map['s_pre_tax_super'].removeClass('x-form-readonly');
										//update the mode to manual
										//(needs to send it {getName: function(){return 's_pre_tax_super_mode';}} as the field because it is expecting an object
										//with the getName() method to tell it which value it needs to update)
										this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'manual', this.processingAllowed);
										//add update listener
										this.getForm().items.map['s_pre_tax_super'].enableKeyEvents = true;
										this.getForm().items.map['s_pre_tax_super'].addListener('keyup', function(field, event) {this.fireEvent('financialdataupdated', this, field, field.getValue(), this.processingAllowed);}, this, {buffer: this.keyup_timeout});
									} else {
										//stops it updating on change
										this.getForm().items.map['s_pre_tax_super'].purgeListeners();
										//makes it readonly
										this.getForm().items.map['s_pre_tax_super'].addClass('x-form-readonly');
										this.getForm().items.map['s_pre_tax_super'].enableKeyEvents = false;
										this.getForm().items.map['s_pre_tax_super'].addListener('focus', function(field){field.blur();});
										//update the mode to auto
										//(need to send it {getName: function(){return 's_pre_tax_super_mode';}} as the field because it is expecting an object
										//with the getName() method to tell it which value it needs to update)
										this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'auto', this.processingAllowed);
									}
								}
							},
							{
								itemId: 's_employer_super',
								name: 'S_EMPLOYER_SUPER',
								readOnly: true,
								cls: 'x-form-readonly',
								fieldLabel: 'Employer Super',
								listeners: {
									focus: function(field)	{field.blur();},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'This is the required employer super contribution.<br /><b><i>This will be automatically calculated</i></b>.'
										});
									}
								}
							},
							{
								width: 140,
				            	itemId: 's_super_fund',
				           		xtype: 'combo',
				           		fieldLabel: 'Is your super fund IOOF?',
				           		name: 'S_SUPER_FUND',
				            	hiddenName: 'S_SUPER_FUND',
				            	hiddenId: 'S_SUPER_FUND_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Yes or No...',
				           		validationEvent: 'blur',
				           		allowBlank: false,
				            	editable:false,
				                mode:'local',
						        hiddenValue: 1,
						        value: 'Yes',
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['ioofCode', 'ioofName'],
				                     data:[[0,'No'],[1,'Yes']]
				                }),
				                displayField:'ioofName',
				                valueField:'ioofCode',
				                
				                listeners: {
									scope: this,
				                	//when index 1, "No", is selected show life cover amount
				                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
				                	select: function(combo, record, index) {
										if (index == 1) {
											combo.nextSibling().expand();
										} else {
											combo.nextSibling().collapse();
											Ext.Msg.alert('Super Fund Change!', 'If you are changing your Super Fund you need to fill out <a href="pdf/superfund_change.pdf" target="_blank">this</a> form for the change to apply.');
										}
										this.fireEvent('financialdataupdated', this, combo, index, false);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Select whether you have nominated IOOF for your super fund or not.<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a super nomination form.</b>'
										});
									}
				                }
				            },
				            {
				            	itemId: 's_additional_life_cover_panel',
				            	xtype: 'panel',
				            	layout: 'form',
				            	//collapsed: true,
				            	defaults: {
				            		width: 140
				            	},
				            	items: [
				            		{
						            	itemId: 's_additional_life_cover',
						           		xtype: 'combo',
						           		fieldLabel: 'Weekly Life Cover Amount',
						           		name: 'S_ADDITIONAL_LIFE_COVER',
						            	hiddenName: 'S_ADDITIONAL_LIFE_COVER',
						            	hiddenId: 'S_ADDITIONAL_LIFE_COVER_hidden',
						           		triggerAction:'all',
						           		emptyText: 'Enter Amount of Life Cover...',
						           		validationEvent: 'blur',
						            	editable:false,
						                mode:'local',
						                hiddenValue: 0,
						                value: '$1',
						                
						                store:new Ext.data.SimpleStore({
						                     fields:['lifecoverCode', 'lifecoverText'],
						                     data:[[0,'$1'],[1,'$2'],[2,'$3'],[3,'$4'],[4,'$5'],[5,'$6'],[6,'$7'],[7,'$8'],[8,'$9'],[9,'$10']]
						                }),
						                displayField:'lifecoverText',
						                valueField:'lifecoverCode',
						                
						                listeners: {
											scope: this,
						                	select: function(combo, record, index) {
												Ext.Msg.alert('Life Cover Change!', 'If you are changing your Life Cover you need to fill out <a href="pdf/ioof_lifecover_change.zip" target="_blank">this</a> form for the change to apply.');
												this.fireEvent('financialdataupdated', this, combo, index, this.processingAllowed);
						                	},
											render: function(c) {
												Ext.QuickTips.register({
													target: c.getEl(),
													text: '<b>Note:</b> This is a weekly figure.<br />CCCA will pay $1/week. If you would like additional Life Cover (on top of the $1/week) record the total amount (including the $1/week to a maximum of $10/week).<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a Life Cover Change form.</b>'
												});
											}
						                }
						            }
				            	]
				            },
				            {
				            	width: 140,
				            	itemId: 's_income_protection_cover_source',
				           		xtype: 'combo',
				           		fieldLabel: 'Where should your Income Protection Cover be taken from?',
				           		name: 'S_INCOME_PROTECTION_COVER_SOURCE',
				            	hiddenName: 'S_INCOME_PROTECTION_COVER_SOURCE',
				            	hiddenId: 'S_INCOME_PROTECTION_COVER_SOURCE_hidden',
				           		triggerAction:'all',
				           		emptyText: 'Enter Source of the Income Protection Cover...',
				           		validationEvent: 'blur',
				            	editable:false,
				                mode:'local',
				                hiddenValue: 0,
				                value: 'Support Account',
				                
				                store:new Ext.data.SimpleStore({
				                     fields:['lifecoversourceCode', 'lifecoversourceText'],
				                     data:[[0,'Support Account'],[1,'Super Fund']]
				                }),
				                displayField:'lifecoversourceText',
				                valueField:'lifecoversourceCode',
				                
				                listeners: {
									scope: this,
				                	select: function(combo, record, index) {
				                		Ext.Msg.alert('INCOME PROTECTION Cover Change!', '<b>You have changed where your INCOME PROTECTION Cover is taken from.<br /><br />Note:<br />This cover is <i>DIFFERENT</i> to your Life Cover.<br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!</b>');
										this.fireEvent('financialdataupdated', this, combo, index, false);
				                	},
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Nominate where your Income Protection Cover should be taken from.<br /><b>This cover is <i>DIFFERENT</i> to your Life Cover.</b><br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!'
										});
									}
				                }
				            }
							
						]
					} //eo spouse super
				]
			}, //eo super
			
		///////////////////////////////MMR//////////////////////////////////////
			
			{
				itemId: 'mmr_panel',
				layout: 'column',
				//title: 'MFB',
				//collapsed: true,
				defaults: {
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						width: 140,
						minValue: 0,
						value: 0
					}
				},
				items: [
					{
						itemId: 'my',
						layout: 'form',
						columnWidth: 0.5,
						title: 'My MMR',
						items: [
							{
								itemId: 'mmr',
								name: 'MMR',
								fieldLabel: 'MMR',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Please record the amount of MMR\'s you plan to claim.'
										});
									}
								}
							}
						]
					}, //eo my mmr
					{
						itemId: 'spouse',
						layout: 'form',
						columnWidth: 0.5,
						title: 'Spouse MMR',
						items: [
							{
								itemId: 's_mmr',
								name: 'S_MMR',
								fieldLabel: 'MMR (Optional)',
								listeners: {
									scope: this,
									render: function(c) {
										Ext.QuickTips.register({
											target: c.getEl(),
											text: 'Please record the amount of MMR\'s you plan to claim.'
										});
									}
								}
							}
						]
					} //eo spouse mmr
				]
			}, //eo mmr
			
		///////////////////////////////International Donations//////////////////////////////////////
			
			{
				itemId: 'international_donations_panel',
				layout: 'form',
				title: 'Incoming International Donations',
				//collapsed: true,
				defaultType: 'numberfield',
				bodyStyle: 'padding:10px',
				labelWidth: 300,
				defaults:{
					width: 140,
					minValue: 0,
					value: 0
				},
				
				items: [
					{
						itemId: 'international_donations',
						name: 'INTERNATIONAL_DONATIONS',
						fieldLabel: 'Incoming International Donations',
						listeners: {
							scope: this,
							render: function(c) {
								Ext.QuickTips.register({
									target: c.getEl(),
									text: 'This is the amount that comes into your CCCA account from any CCC account outside of Australia (eg your CCCI account, if you have one.)'
								});
							}
						}
					}
				]
			}, //eo international donations
			
		///////////////////////////////Internal Transfers//////////////////////////////////////
			
			new tmn.view.InternalTransfers() //look in InternalTransfers.js
			
		] // eo form items
		
			///////////////////////////////Validation Bar//////////////////////////////////////
		/*bbar: new Ext.ux.StatusBar({
			defaultText: 'Ready',
			plugins: new Ext.ux.ValidationStatus({form: this.id}) //change test_form to id from above (ln 6)
		})*/
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.FinancialDetailsForm.superclass.constructor.call(this, this.config);
	
	//go through each field in the form and add the change listener to the fields that aren't read only
	//needs to be here because the config needs to be applied in the above call for it to be able to check if it is read only
	this.getForm().items.each(function(item, index, length) {								//go though each form field
		if (item.cls === undefined) {														//any field that is read only should have cls registered so the user can see its read only
			item.enableKeyEvents = true;
			item.on('keyup', function(field, event) {
				this.fireEvent('financialdataupdated', this, field, field.getValue(), this.processingAllowed);	//add listener
				//mark form as unsaved
				this.saved	= false;
			}, this, {buffer: this.keyup_timeout});
		}
	}, this);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.FinancialDetailsForm, Ext.FormPanel, {
	
	/**
	 * Returns whether the loaded session is saved or not
	 * 
	 * @returns {bool}		Whether the loaded session is locked or not
	 */
	isSaved: function() {
		return (this.saved && this.getComponent('internal_transfers_panel').isSaved());
	},
	
	/**
	 * Returns whether the loaded session is locked or not
	 * 
	 * @returns {bool}		Whether the loaded session is locked or not
	 */
	sessionIsLocked: function() {
		var session_store	= this.getTopToolbar().items.map['session_combo'].getStore();
		var session_record	= session_store.getAt(session_store.find('SESSION_ID' , this.getSession() + ''));

		if (session_record !== undefined) {

			if (session_record.data.LOCKED == 'true' || session_record.data.LOCKED == 'TRUE' || session_record.data.LOCKED == 'True' || session_record.data.LOCKED == true) {
				return true;
			} else {
				return false;
			}
			
		} else {
			return false;
		}
	},
	
	/**
	 * Sets the session as locked and stops the user from saving or deleting the session,
	 * they can only load a new one, save as a new version or reset the session
	 */
	lock: function() {
		Ext.MessageBox.show({
			icon: Ext.MessageBox.WARNING,
			buttons: Ext.MessageBox.OK,
			closable: false,
			title: 'Warning!',
			msg: "This session is Locked because it has been submitted. You can't save your changes to this session or delete this session. If you would like to save changes to this session, please use Save As to save a new version of the session."
		});
		this.getTopToolbar().items.map['save_session_button'].disable();
		this.getTopToolbar().items.map['delete_session_button'].disable();
		this.locked = true;
	},
	
	/**
	 * Sets the session as unlocked and allows the user to do what they will to the session.
	 */
	unlock: function() {
		this.getTopToolbar().items.map['save_session_button'].enable();
		this.getTopToolbar().items.map['delete_session_button'].enable();
		this.locked = false;
	},
	
	/**
	 * Returns the session id of the session selected in the session combo
	 */
	getSelectedSession: function() {
		var value = this.getTopToolbar().items.map['session_combo'].getValue();
		if (!(value === undefined || value == null || value == '')) {
			return this.getTopToolbar().items.map['session_combo'].getValue();
		} else {
			return this.getSession();
		}
	},
	
	/**
	 * Sets the session selected in the session combo
	 */
	setSelectedSession: function(session) {
		//make sure the value is a string
		session += '';
		//set the combos value
		this.getTopToolbar().items.map['session_combo'].setValue(session);
	},
	
	/**
	 * Returns the current session that is being modified.
	 * @returns {number}			A number that is the id of the user's session.
	 */
	getSession: function() {return this.financial_data.session_id;},
	
	/**
	 * Sets the id of the current session that is being modified.
	 * @param {number}	session		The number representing the user's session.
	 */
	setSession: function(session) {

		//if this session is in the combo then grab the session name and set the session name to the found name
		var sessionStore	= this.getTopToolbar().items.map['session_combo'].getStore(),
		sessionRecordId		= sessionStore.find('SESSION_ID', session + '');
		if(sessionRecordId >= 0) {
			this.setSessionName(sessionStore.getAt(sessionRecordId).data.SESSION_NAME);
		}

		this.financial_data.session_id = session;
		this.getComponent('internal_transfers_panel').setSession(session);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'session_id';}}, session, false);
	},
	/**
	 * Returns the current session that is being modified.
	 * @returns {number}			A number that is the id of the user's session.
	 */
	getSessionName: function() {return this.financial_data.session_name;},
	
	/**
	 * Sets the id of the current session that is being modified.
	 * @param {number}	session		The number representing the user's session.
	 */
	setSessionName: function(name) {
		this.financial_data.session_name = name;
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'session_name';}}, name, false);
	},
	
	/**
	 * Runs through all the names in the session combo and returns true if it finds name in that list
	 * 
	 * @returns {bool}
	 */
	nameAlreadyExists: function(session_name) {
		//grab the store that holds the session names and ids
		var sessionStore	= this.getTopToolbar().items.map['session_combo'].getStore();
		//if the name is found in the store return true
		if(sessionStore.findExact('SESSION_NAME',session_name) >= 0) {
			return true;
		} else {
			return false;
		}
	},
	
	/**
	 * Returns whether the user has a spouse or not.
	 * @returns {boolean}			A boolean that tell you if the user has a spouse.
	 */
	hasSpouse: function() {return this.financial_data.spouse;},
	
	/**
	 * Lets this form know if the user has a spouse or not.
	 * @param {boolean}	spouse		A boolean that tell you if the user has a spouse.
	 */
	setSpouse: function(spouse) {this.financial_data.spouse = spouse;},
	
	/**
	 * Returns whether the user serves overseas or not.
	 * @returns {boolean}			A boolean that tell you if the user serves overseas.
	 */
	isOverseas: function() {return this.financial_data.overseas;},
	
	/**
	 * Lets this form know if the user serves overseas or not.
	 * @param {boolean}	overseas	A boolean that tell you if the user serves overseas.
	 */
	setOverseas: function(overseas) {this.financial_data.overseas = overseas;},
	
	/**
	 * Gets the start date of the assignment.
	 * returns {string}	date	A string that represents the start date of the assignment. The format of the string will match the format property of the date field (ie d/m/Y).
	 */
	enableStartDate: function() {
		this.getForm().items.map['os_assignment_start_date'].enable();	
	},
	
	/**
	 * Sets the start date of the assignment.
	 * @param {string}	date	A string that can be parsed into a valid date. The format of the string must match the format property of the date field (ie d/m/Y).
	 */
	disableStartDate: function(date) {
		this.getForm().items.map['os_assignment_start_date'].disable();	
	},

	/**
	 * Returns a reference to the object that defines the end date field.
	 * @returns {Ext.form.DateField}			The reference to the end date field.
	 */
	endDate: function() {
		return this.getForm().items.map['os_assignment_end_date'];
	},
	
	/**
	 * Changes the forms visual structure so that it only displays what is necessary for a single missionary.
	 * It will also disable irrelivant fields aswell as hiding them so that it doesn't effect the validation.
	 */
	doSingleLayout: function() {
		//hide taxable income spouse
		var fieldset = this.getComponent('taxable_income_panel');
		fieldset.getComponent('spouse').hide();
		fieldset.getComponent('my').columnWidth = 1;
		
		//hide mfb spouse
		fieldset = this.getComponent('mfb_panel');
		fieldset.getComponent('spouse').hide();
		fieldset.getComponent('my').columnWidth = 1;
		
		//hide lafha spouse
		fieldset = this.getComponent('os_lafha_panel');
		fieldset.getComponent('spouse').hide();
		fieldset.getComponent('my').columnWidth = 1;
		
		//hide super details spouse
		fieldset = this.getComponent('super_panel');
		fieldset.getComponent('spouse').hide();
		fieldset.getComponent('my').columnWidth = 1;
		
		//hide mmr spouse
		fieldset = this.getComponent('mmr_panel');
		fieldset.getComponent('spouse').hide();
		fieldset.getComponent('my').columnWidth = 1;
		
		//disable spouse fields
		this.form.items.each(function (item, index, length) {
			if (item.getItemId().substr(0,2) == 's_'){
				item.disable();
			}
		});
		
		this.doLayout();
	},
	
	/**
	 * Changes the forms visual structure so that it only displays what is necessary for a married missionary.
	 * It will also disable irrelivant fields aswell as hiding them so that it doesn't effect the validation.
	 */
	doMarriedLayout: function() {
		//show taxable income spouse
		var fieldset = this.getComponent('taxable_income_panel');
		fieldset.getComponent('my').columnWidth = 0.5;
		fieldset.getComponent('spouse').show();
		
		//show mfb spouse
		fieldset = this.getComponent('mfb_panel');
		fieldset.getComponent('my').columnWidth = 0.5;
		fieldset.getComponent('spouse').show();
		
		//show lafha spouse
		fieldset = this.getComponent('os_lafha_panel');
		fieldset.getComponent('my').columnWidth = 0.5;
		fieldset.getComponent('spouse').show();
		
		//show super details spouse
		fieldset = this.getComponent('super_panel');
		fieldset.getComponent('my').columnWidth = 0.5;
		fieldset.getComponent('spouse').show();
		
		//show mmr spouse
		fieldset = this.getComponent('mmr_panel');
		fieldset.getComponent('my').columnWidth = 0.5;
		fieldset.getComponent('spouse').show();
		
		//enable spouse fields
		this.form.items.each(function (item, index, length) {
			if (item.getItemId().substr(0,2) == 's_'){
				item.enable();
			}
		});
		
		this.doLayout();
	},
	
	/**
	 * Changes the forms visual structure so that it only displays what is necessary for an overseas missionary.
	 * It will also disable irrelivant fields aswell as hiding them so that it doesn't effect the validation.
	 */
	doOverseasLayout: function() {
		//hide aussie stuff
		this.getComponent('housing_panel').items.each(function (item, index, length) {item.disable();}); //disable all the o/s add extras fields
		this.getComponent('housing_panel').hide();
		this.getComponent('mfb_panel').getComponent('my').items.each(function (item, index, length) {item.disable();}); //disable all the mfb fields
		if (this.hasSpouse()) this.getComponent('mfb_panel').getComponent('spouse').items.each(function (item, index, length) {item.disable();}); //disable all the mfb fields
		this.getComponent('mfb_panel').hide();
		this.getComponent('os_overseas_housing_panel').items.each(function (item, index, length) {item.disable();}); //disable all the o/s add extras fields
		this.getComponent('os_overseas_housing_panel').hide();
		
		//show overseas stuff
		this.getComponent('taxable_income_panel').getComponent('os_residency').show();
		this.getComponent('os_assignment_panel').show();
		this.getComponent('os_assignment_panel').items.each(function (item, index, length) {item.enable();}); //enable all the o/s add extras fields
		this.getComponent('os_lafha_panel').show();
		this.getComponent('os_lafha_panel').getComponent('my').items.each(function (item, index, length) {item.enable();}); //disable all the lafha fields
		if (this.hasSpouse()) this.getComponent('os_lafha_panel').getComponent('spouse').items.each(function (item, index, length) {item.enable();}); //disable all the lafha fields
	},
	
	/**
	 * Changes the forms visual structure so that it only displays what is necessary for an overseas missionary on home assignment.
	 * It will also disable irrelivant fields aswell as hiding them so that it doesn't effect the validation.
	 */
	doHomeAssignmentLayout: function() {
		//hide aussie stuff & international stuff
		this.getComponent('os_lafha_panel').getComponent('my').items.each(function (item, index, length) {item.disable();}); //disable all the lafha fields
		if (this.hasSpouse()) this.getComponent('os_lafha_panel').getComponent('spouse').items.each(function (item, index, length) {item.disable();}); //disable all the lafha fields
		this.getComponent('os_lafha_panel').hide();
		
		//show overseas stuff
		this.getComponent('taxable_income_panel').getComponent('os_residency').show();
		this.getComponent('os_assignment_panel').show();
		this.getComponent('os_assignment_panel').items.each(function (item, index, length) {item.enable();}); //enable all the o/s add extras fields
		this.getComponent('housing_panel').show();
		this.getComponent('housing_panel').items.each(function (item, index, length) {item.enable();}); //enable all the o/s add extras fields
		this.getComponent('mfb_panel').show();
		this.getComponent('mfb_panel').getComponent('my').items.each(function (item, index, length) {item.enable();}); //disable all the mfb fields
		if (this.hasSpouse()) this.getComponent('mfb_panel').getComponent('spouse').items.each(function (item, index, length) {item.enable();}); //disable all the mfb fields
		this.getComponent('os_overseas_housing_panel').show();
		this.getComponent('os_overseas_housing_panel').items.each(function (item, index, length) {item.enable();}); //enable all the o/s add extras fields
	},
	
	/**
	 * Changes the forms visual structure so that it only displays what is necessary for an aussie based missionary.
	 * It will also disable irrelivant fields aswell as hiding them so that it doesn't effect the validation.
	 */
	doAussieLayout: function() {
		//hide overseas stuff
		this.getComponent('taxable_income_panel').getComponent('os_residency').hide(); //not disabled because we always need to send this data its just aussie based missionaries don't get a choice
		this.getComponent('os_assignment_panel').items.each(function (item, index, length) {item.disable();}); //disable all the o/s add extras fields
		this.getComponent('os_assignment_panel').hide();
		this.getComponent('os_lafha_panel').getComponent('my').items.each(function (item, index, length) {item.disable();}); //disable all the lafha fields
		if (this.hasSpouse()) this.getComponent('os_lafha_panel').getComponent('spouse').items.each(function (item, index, length) {item.disable();}); //disable all the lafha fields
		this.getComponent('os_lafha_panel').hide();
		this.getComponent('os_overseas_housing_panel').items.each(function (item, index, length) {item.disable();}); //disable all the o/s add extras fields
		this.getComponent('os_overseas_housing_panel').hide();
		
		//show aussie stuff
		this.getComponent('housing_panel').show();
		this.getComponent('housing_panel').items.each(function (item, index, length) {item.enable();}); //enable all the o/s add extras fields
		this.getComponent('mfb_panel').show();
		this.getComponent('mfb_panel').getComponent('my').items.each(function (item, index, length) {item.enable();}); //disable all the mfb fields
		if (this.hasSpouse()) this.getComponent('mfb_panel').getComponent('spouse').items.each(function (item, index, length) {item.enable();}); //disable all the mfb fields
	},
	
	/**
	 * Will load the form with an ajax request to php/load_financial_details.php.
	 * It will fire the loadsuccess or loadfailure when the ajax request returns.
	 */
	loadForm: function (local_data) {
		//hide stuff that shouldnt be seen
		if (this.hasSpouse()){
			this.doMarriedLayout();
		} else {
			this.doSingleLayout();
		}
		
		//checks if stuff should be hidden ( uses !(valid condition) because we are not just checking valid we are also checking if its set )
		if (!(this.getForm().items.map['housing_stipend'].getValue() > 0)){
			this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').hide();
			this.getComponent('taxable_income_panel').getComponent('spouse').getComponent('hs').hide();
		}
		
		//change the visual layout of the form based on if this instance of the class is for an overseas, overseas while on home assignment or aussie based missionary.
		if (this.isOverseas()) {
			if (this.home_assignment == true) {
				this.doHomeAssignmentLayout();
			} else {
				this.doOverseasLayout();
			}
		} else {
			this.doAussieLayout();
		}
		
		//if a session has been set prior to this to be loaded then load the session
		if (this.getSession() !== undefined && this.getSession() != '' && this.getSession() != null) {
			//console.info('loadForm: load session url');
			//load this session
			this.fireEvent('loadsession', this);
			
			//load grid
			this.getComponent('internal_transfers_panel').loadInternalTransfers(this.getSession());
			
		//if no session needs to be loaded then load local data or defaults
		} else {
			
			this.fireEvent('resetsession', this);
		/*
			//load grid
			this.getComponent('internal_transfers_panel').loadInternalTransfers(this.getSession());
			
			//load fields with local data
			if (local_data === undefined) {
				//console.info('loadForm: load url');
				//if no local data is avaiblable grab the data from the backend
				this.load({
					url: this.load_url,
					waitMsgTarget: true,
					waitMsg: 'Loading',
					params:{mode:'get'},
					scope: this,
					success: function(form, action) {
						this.fireEvent('loadsuccess', this, form, action);
					},
					failure: function(form, action) {
						this.fireEvent('loadfailure', this, form, action);
					}
				});
			//otherwise load defaults
			} else {
				
				//console.info('loadForm: load defaults, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
				//update backend with defaults
				this.fireEvent('financialdataupdated', this, this.getForm().items.map['os_resident_for_tax_purposes'], this.getForm().items.map['os_resident_for_tax_purposes'].getValue(), false);
				this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'home_assignment';}}, this.home_assignment, false);
				this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'auto', false);
				this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'auto', false);
				this.fireEvent('financialdataupdated', this, this.getForm().items.map['mfb_rate'], this.getForm().items.map['mfb_rate'].getValue(), false);
				this.fireEvent('financialdataupdated', this, this.getForm().items.map['s_mfb_rate'], this.getForm().items.map['s_mfb_rate'].getValue(), false);
				
				//load values from last form
				for (field in local_data) {
					if (this.getForm().items.map[field.toLowerCase()] !== undefined) {
						this.getForm().items.map[field.toLowerCase()].setValue(local_data[field]);
						this.fireEvent('financialdataupdated', this, this.getForm().items.map[field.toLowerCase()], local_data[field], false);
					}
				}
				
				//convert any strings to numbers (ie "0" to 0)
				for (fieldCount = 0; fieldCount < this.getForm().items.length; fieldCount++){
					if ( !isNaN(parseInt(this.getForm().items.items[fieldCount].getValue())) )
						this.getForm().items.items[fieldCount].setValue(parseInt(this.getForm().items.items[fieldCount].getValue()));
				}
			}
		*/
		}
	},
	
	/**
	 * This handler will load defaults, correct type problems from sending the data (ie string to int). It also triggers any events needed based on the values returned.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that succeeded (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadSuccess: function (form, action) {
		//convert any strings to numbers (ie "0" to 0)
		for (fieldCount = 0; fieldCount < form.items.length; fieldCount++){
			if ( !isNaN(parseInt(form.items.items[fieldCount].getValue())) )
				form.items.items[fieldCount].setValue(parseInt(form.items.items[fieldCount].getValue()));
		}
		//console.info('onLoadSuccess: load defaults, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
		//update backend with defaults
		this.fireEvent('financialdataupdated', this, form.items.map['os_resident_for_tax_purposes'], form.items.map['os_resident_for_tax_purposes'].getValue(), false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'home_assignment';}}, this.home_assignment, false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, form.items.map['mfb_rate'], form.items.map['mfb_rate'].getValue(), false);
		this.fireEvent('financialdataupdated', this, form.items.map['s_mfb_rate'], form.items.map['s_mfb_rate'].getValue(), false);
		//fires an update event that will send an ajax request
		this.fireEvent('financialdataupdated', this, form.items.map['mmr'], form.items.map['mmr'].getValue(), this.processingAllowed);
	},
	
	/**
	 * Will display error messages based on the error that caused the failure. (It also runs the success code because this handler is often falsely triggered)
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadFailure: function (form, action){
		if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
			Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>');
			return;
		}
		
		//convert any strings to numbers (ie "0" to 0)
		for (fieldCount = 0; fieldCount < form.items.length; fieldCount++){
			if ( !isNaN(parseInt(form.items.items[fieldCount].getValue())) )
				form.items.items[fieldCount].setValue(parseInt(form.items.items[fieldCount].getValue()));
		}
		//console.info('onLoadFailure: load defaults, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
		//update backend with defaults
		this.fireEvent('financialdataupdated', this, form.items.map['os_resident_for_tax_purposes'], form.items.map['os_resident_for_tax_purposes'].getValue(), false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'home_assignment';}}, this.home_assignment, false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, form.items.map['mfb_rate'], form.items.map['mfb_rate'].getValue(), false);
		this.fireEvent('financialdataupdated', this, form.items.map['s_mfb_rate'], form.items.map['s_mfb_rate'].getValue(), false);
		//fires an update event that will send an ajax request
		this.fireEvent('financialdataupdated', this, form.items.map['mmr'], form.items.map['mmr'].getValue(), this.processingAllowed);
	},
	
	/**
	 * Will submit the form with an ajax request to php/submit_financial_details.php.
	 * It sends the request with all the enabled field's values.
	 * It will fire the submitsuccess or submitfailure when the ajax request returns.
	 */
	submitForm: function() {
		//hide last TMN window
		tmn.view.LastTMN.hide();
		
		//submit fields
		this.form.submit({
			url: this.submit_url,
			params: {session: this.getSession(), aussie_form: this.aussie_form, overseas_form: this.overseas_form, home_assignment: this.home_assignment},
			scope: this,
			success: function (form, action) {
				this.fireEvent('submitsuccess', this, form, action);
			},
			failure: function (form, action) {
				this.fireEvent('submitfailure', this, form, action);
			}
		});
		
	},
	
	/**
	 * Will save the reponse of the submit request.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitSuccess: function (form, action) {
		this.response = action.response.responseText;
		//console.log("No Error Detected");
	},
	
	/**
	 * Will display error messages based on the error that caused the failure.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitFailure: function (form, action){
		//console.log("Error Detected:", action.failureType, action.result);
        switch (action.failureType) {
            case Ext.form.Action.CLIENT_INVALID:
            	Ext.Msg.show({icon: Ext.MessageBox.WARNING, buttons: Ext.MessageBox.OK, closable: false, title: 'User Error', msg: 'Form fields may not be submitted with invalid values. The Toolbar at the top of the page has a list of errors to assist you.'});
            	this.getTopToolbar().plugins.onSubmitFailure();
                break;
            case Ext.form.Action.CONNECT_FAILURE:
    			Ext.Msg.show({icon: Ext.MessageBox.WARNING, buttons: Ext.MessageBox.OK, closable: false, title: 'Server Error', msg: 'Could Not Connect to Server! Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>'});
                break;
            case Ext.form.Action.SERVER_INVALID:
            	if (action.result.alert !== undefined) {
            		Ext.Msg.show({icon: Ext.MessageBox.WARNING, buttons: Ext.MessageBox.OK, closable: false, title: 'Server Error', msg: action.result.alert});
            	}
            	
            	if (action.result.errors !== undefined) {
            		
            		var fieldArray = this.getForm().items.map;
            		
            		Ext.MessageBox.show({icon: Ext.MessageBox.ERROR, buttons: Ext.MessageBox.OK, closable: false, title: 'Error!', msg: "You have errors in your form, please review your numbers and try again."});
					
    				//go through all the errors and mark the appropriate fields as invalid
    				for (error in action.result.errors) {											//error is the key of the associative array
    					fieldArray[error.toLowerCase()].markInvalid(action.result.errors[error]);	//using the key, error, mark the field as invalid with the error message at return_object.errors[error]
    				}
    			}
            	
            	break;
       }
	},
	
				///////////////////////////////Process Financial Data Code//////////////////////////////////////
	/**
	 * Handler for when the user updates a piece of financial data and that data is successfully processed.
	 * It will hide or show any fields required, show any errors and display any warning messages.
	 * 
	 * @param {Object}				financial_data: 	The set of data just returned from the process financial data ajax request. It will include data collected from the user as well as calculated values.<br />
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onProcessFinancialDataSuccess: function(financial_data, response, options) {
		//update the financial data with the updated values from the BE
		var return_object = Ext.util.JSON.decode(response.responseText);
		var fieldArray = this.getForm().items.map;

		//check if it returned data or errrors
		if (return_object.success == true || return_object.success == 'true'){
			this.financial_data = financial_data;

			//go through all the returned values and put them in their appropriate fields
			for (field in this.financial_data) {
				if (fieldArray[field.toLowerCase()] !== undefined) {						//if the field exists
					fieldArray[field.toLowerCase()].setValue(this.financial_data[field]);	//set the value of the field to the processed value
				}
			}

			//hide/show the spouse's housing stipend as needed
			if (this.financial_data.S_HOUSING_STIPEND !== undefined && this.financial_data.S_HOUSING_STIPEND > 0){
				this.getComponent('taxable_income_panel').getComponent('spouse').getComponent('hs').show();
			} else {
				this.getComponent('taxable_income_panel').getComponent('spouse').getComponent('hs').hide();
			}

			//hide/show the housing stipend as needed
			if (this.financial_data.HOUSING_STIPEND !== undefined && this.financial_data.HOUSING_STIPEND > 0){
				this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').show();
			} else {
				this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').hide();
			}

			if (return_object.warnings !== undefined) {
				//go through all the errors and mark the appropriate fields as invalid
				for (warning in return_object.warnings) {											//error is the key of the associative array
					Ext.MessageBox.show({icon: Ext.MessageBox.WARNING, buttons: Ext.MessageBox.OK, closable: false, title: 'Warning!', msg: return_object.warnings[warning]});
					fieldArray[warning.toLowerCase()].markInvalid(return_object.warnings[warning]);	//using the key, error, mark the field as invalid with the error message at return_object.errors[error]
				}
			}

		} else {
			//go through all the errors and mark the appropriate fields as invalid
			for (error in return_object.errors) {											//error is the key of the associative array
				fieldArray[error.toLowerCase()].markInvalid(return_object.errors[error]);	//using the key, error, mark the field as invalid with the error message at return_object.errors[error]
			}
		}
		
	},
	
	/**
	 * Handler for when the user updates a piece of financial data and that data fails to be processed.
	 * It will show error messages based on what caused the error.
	 * 
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onProcessFinancialDataFailure: function(response, options) {
		Ext.MessageBox.alert('Server Error', 'Server Could Not Calculate Values! Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>');
	},
	
	/**
	 * Handler for when the user selects to load a session.
	 */
	onLoadSession: function(data, inflate) {
		var loadParams;
		//hide last TMN window
		tmn.view.LastTMN.hide();
		
		if (this.rendered) {
			this.el.mask("Loading");
		}

		//set params based on whether the user wants to inflate or not
		if (inflate !== undefined && inflate == true) {
			loadParams	= {
				mode:		'r',
				form:		Ext.util.JSON.encode({aussie_form:this.aussie_form,overseas_form:this.overseas_form,home_assignment:this.home_assignment}),
				data:		Ext.util.JSON.encode(data),
				inflate:	'true'
			};
		} else {
			loadParams	= {
				mode:		'r',
				form:		Ext.util.JSON.encode({aussie_form:this.aussie_form,overseas_form:this.overseas_form,home_assignment:this.home_assignment}),
				data:		Ext.util.JSON.encode(data)
			};
		}
		
		//load session
		Ext.Ajax.request({											//send all the data about the misso to the server for processing
			url: this.crud_url,
			params: loadParams,
			success: function(response, options){
				this.fireEvent('loadsessionsuccess', this, response, options);
			},
			failure: this.onLoadSessionFailure,
			scope: this
		});
	},
	
	/**
	 * Handler for when the user selects to load a session and that load succeeds.
	 * 
	 * @param {Object} 				data: 			An assoc array of the parsed json that was returned from the server
	 */
	onLoadSessionSuccess: function(data) {
		
		//load the data into the form
		if (data !== undefined) {
			
			//if the form has been rendered load the data into it
			if (this.rendered) {
				//save the session combo's state before the form is reset
				var sessionIDtemp = data['session_id'];
				//console.info('onLoadSessionSuccess: call resetForm, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
				
				this.resetForm();
				
				//stop financial data being sent to the backend for processing while data is loaded into the form
				this.processingAllowed	= false;
				
				//checks if housing stipend should be hidden
				if ((data['housing_stipend'] !== undefined && data['housing_stipend'] > 0) || (data['s_housing_stipend'] !== undefined && data['s_housing_stipend'] > 0)) {
					this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').show();
					this.getComponent('taxable_income_panel').getComponent('spouse').getComponent('hs').show();
				} else {
					this.getComponent('taxable_income_panel').getComponent('my').getComponent('hs').hide();
					this.getComponent('taxable_income_panel').getComponent('spouse').getComponent('hs').hide();
				}
				
				//load pre tax super mode first if it exists
				if (data['pre_tax_super_mode'] !== undefined) {
					if (data['pre_tax_super_mode'] == 'manual') {
						this.getComponent('super_panel').getComponent('my').getComponent('pre_tax_super_panel').getComponent('pre_tax_super_mode').toggle(true);
					} else {
						this.getComponent('super_panel').getComponent('my').getComponent('pre_tax_super_panel').getComponent('pre_tax_super_mode').toggle(false);
					}
				}
				
				//load pre tax super mode first if it exists
				if (data['s_pre_tax_super_mode'] !== undefined) {
					if (data['s_pre_tax_super_mode'] == 'manual') {
						this.getComponent('super_panel').getComponent('spouse').getComponent('pre_tax_super_panel').getComponent('s_pre_tax_super_mode').toggle(true);
					} else {
						this.getComponent('super_panel').getComponent('spouse').getComponent('pre_tax_super_panel').getComponent('s_pre_tax_super_mode').toggle(false);
					}
				}
				
				//checks if super fund should be hidden
				if ((data['super_fund'] !== undefined && data['super_fund'] == 1)) {
					this.getComponent('super_panel').getComponent('my').getComponent('additional_life_cover_panel').expand();
				} else {
					this.getComponent('super_panel').getComponent('my').getComponent('additional_life_cover_panel').collapse();
				}
				
				//checks if spouse super fund should be hidden
				if ((data['s_super_fund'] !== undefined && data['s_super_fund'] == 1)) {
					this.getComponent('super_panel').getComponent('spouse').getComponent('s_additional_life_cover_panel').expand();
				} else {
					this.getComponent('super_panel').getComponent('spouse').getComponent('s_additional_life_cover_panel').collapse();
				}
				
				for (field in data) {
					if (this.getForm().items.map[field.toLowerCase()] !== undefined) {
						this.getForm().items.map[field.toLowerCase()].setValue(data[field]);
						this.fireEvent('financialdataupdated', this, this.getForm().items.map[field.toLowerCase()], data[field], false);
					} else {
						
						this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return this.name;}, name:field}, data[field], false);
					}
				}
				
				//convert any strings to numbers (ie "0" to 0)
				for (fieldCount = 0; fieldCount < this.getForm().items.length; fieldCount++){
					if ( !isNaN(parseInt(this.getForm().items.items[fieldCount].getValue())) )
						this.getForm().items.items[fieldCount].setValue(parseInt(this.getForm().items.items[fieldCount].getValue()));
				}
				
				//if no start date is loaded reset date field validation
				if (this.getForm().items.map['os_assignment_start_date'].getValue() === undefined || this.getForm().items.map['os_assignment_start_date'].getValue() == null || this.getForm().items.map['os_assignment_start_date'].getValue() == '') {
					this.getForm().items.map['os_assignment_start_date'].setMaxValue(null);
					this.getForm().items.map['os_assignment_start_date'].setMinValue(null);
				} else {
					this.getForm().items.map['os_assignment_start_date'].setMaxValue(this.getForm().items.map['os_assignment_start_date'].getValue());
					this.getForm().items.map['os_assignment_start_date'].setMinValue(null);
				}
				
				//if no end date is loaded reset date field validation
				if (this.getForm().items.map['os_assignment_end_date'].getValue() === undefined || this.getForm().items.map['os_assignment_end_date'].getValue() == null || this.getForm().items.map['os_assignment_end_date'].getValue() == '') {
					this.getForm().items.map['os_assignment_end_date'].setMaxValue(null);
					this.getForm().items.map['os_assignment_end_date'].setMinValue(null);
				} else {
					this.getForm().items.map['os_assignment_end_date'].setMaxValue(null);
					this.getForm().items.map['os_assignment_end_date'].setMinValue(this.getForm().items.map['os_assignment_start_date'].getValue());
				}
			
				//put the session combo's value back
				this.setSelectedSession(sessionIDtemp);
				if (!isNaN(parseInt(sessionIDtemp))) {
					sessionIDtemp = parseInt(sessionIDtemp);
				}
				//set the form's session now that it is loaded
				this.setSession(sessionIDtemp);
				
				//if the session has been submitted then set it as locked
				if (this.sessionIsLocked()) {//data.auth_session_id !== undefined) {
					this.lock();
				} else {
					this.unlock();
				}
	
				//load the session's internal transfers
				this.getComponent('internal_transfers_panel').loadInternalTransfers(this.getSession());
				
				//restart financial data being sent to the backend for processing now that the data is loaded
				this.processingAllowed	= true;
			
				//mark form as saved
				this.saved = true;

			//if the form hasn't been rendered yet just store the session to be loaded once it has been rendered
			} else {
				this.setSession(data['session_id']);
			}

		}
		
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
	},
	
	/**
	 * Handler for when the user selects to load a session and that load fails.
	 * 
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onLoadSessionFailure: function(response, options) {
		Ext.MessageBox.alert('Message', 'Load Failure, Try Again!');
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
	},
	
	/**
	 * Handler for when the user selects to save a session.
	 */
	onSaveAsSession: function(data) {
		
		//if the form is rendered then add a mask
		if (this.rendered) {
			this.el.mask("Saving Session: " + this.getSessionName());
		}
		
		//create session
		Ext.Ajax.request({
			url: this.crud_url,
			params: {
				mode: 'c',
				form: Ext.util.JSON.encode({aussie_form:this.aussie_form,overseas_form:this.overseas_form,home_assignment:this.home_assignment}),
				data: Ext.util.JSON.encode(data)
			},
			success: function(response, options){
				this.fireEvent('saveassessionsuccess', this, response, options);
			},
			failure: this.onSaveSessionFailure,
			scope: this
		});
	},
	
	saveAsInternalTransfers: function(session_id) {
		//copy all the internal transfers to the newly created session
		//(done here so only one copy gets created and we don't have duplicates)
		this.getComponent('internal_transfers_panel').saveAsInternalTransfers(session_id);
	},
	
	/**
	 * Handler for when the user selects to save as a session and that save succeeds.
	 * 
	 * @param {int} id	- session id of the newly created session
	 */
	onSaveAsSessionSuccess: function(id) {
		
		this.onSaveSessionSuccess();
		
	},
	
	/**
	 * Handler for when the user selects to save a session.
	 */
	onSaveSession: function(data) {
		
		//if the form is rendered then add a mask
		if (this.rendered) {
			this.el.mask("Saving");
		}
			
		//update session
		Ext.Ajax.request({
			url: this.crud_url,
			params: {
				mode: 'u',
				form: Ext.util.JSON.encode({aussie_form:this.aussie_form,overseas_form:this.overseas_form,home_assignment:this.home_assignment}),
				data: Ext.util.JSON.encode(data)
			},
			success: this.onSaveSessionSuccess,
			failure: this.onSaveSessionFailure,
			scope: this
		});
		
		//save the internal transfers too
		this.getComponent('internal_transfers_panel').saveInternalTransfers();
	},
	
	/**
	 * Handler for when the user selects to save a session and that save succeeds.
	 */
	onSaveSessionSuccess: function() {
		
		//if the session has been submitted then set it as locked
		if (this.sessionIsLocked()) { //data.auth_session_id !== undefined) {
			this.lock();
		} else {
			this.unlock();
		}
		
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
		
		//mark form as saved
		this.saved = true;
	},
	
	reloadSessionCombo: function() {
		this.getTopToolbar().items.map['session_combo'].getStore().reload({
			callback:function() {
				if (this.getSession() != null) {
					this.setSelectedSession(this.getSession());					
				} else {
					this.getTopToolbar().items.map['session_combo'].clearValue();
				}
			},
			scope:this
		});
	},
	
	/**
	 * Handler for when the user selects to save a session and that save fails.
	 * 
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onSaveSessionFailure: function(response, options) {
		Ext.MessageBox.alert('Message', 'Save Failure, Try Again!');
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
	},
	
	/**
	 * Handler for when the user selects to delete a session.
	 */
	onDeleteSession: function(data) {
		
		//hide last TMN window
		tmn.view.LastTMN.hide();
		
		//if the form is rendered then add a mask
		if (this.rendered) {
			this.el.mask("Deleting");
		}
		
		//delete all the internal transfers for this session
		this.getComponent('internal_transfers_panel').deleteInternalTransfers();
		
		//delete session
		Ext.Ajax.request({											//send all the data about the misso to the server for processing
			url: this.crud_url,
			params: {
				mode: 'd',
				form: Ext.util.JSON.encode({aussie_form:this.aussie_form,overseas_form:this.overseas_form,home_assignment:this.home_assignment}),
				data: Ext.util.JSON.encode(data)
			},
			success: function(response, options){
				this.fireEvent('deletesessionsuccess', this, response, options);
			},
			failure: this.onDeleteSessionFailure,
			scope: this
		});
		
	},
	
	/**
	 * Handler for when the user selects to delete a session and that delete succeeds.
	 * 
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onDeleteSessionSuccess: function(response, options) {
		
		//reload the session combo box
		this.getTopToolbar().items.map['session_combo'].getStore().reload();
		
		this.resetForm();
		
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
		
		//mark form as saved
		this.saved = true;
	},
	
	/**
	 * Handler for when the user selects to delete a session and that delete fails.
	 * 
	 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
	 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
	 * @param {Object} 				options: 			The parameter to the request call.
	 */
	onDeleteSessionFailure: function(response, options) {
		Ext.MessageBox.alert('Message', 'Delete Failure, try again!');
		//if the form is rendered then remove any masks
		if (this.rendered) {
			this.el.unmask();
		}
	},
	
	resetForm: function() {
		
		//reset financial data
		this.fireEvent('resetfinancialdata', this);
		
		//reset date field validation
		this.getForm().items.map['os_assignment_start_date'].setMaxValue(null);
		this.getForm().items.map['os_assignment_start_date'].setMinValue(null);
		this.getForm().items.map['os_assignment_end_date'].setMaxValue(null);
		this.getForm().items.map['os_assignment_end_date'].setMinValue(null);
		
		//clear all fields
		for (fieldCount = 0; fieldCount < this.getForm().items.length; fieldCount++){
			this.getForm().items.items[fieldCount].reset();
		}
		//console.info('resetForm: load defaults, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
		//update backend with defaults
		//TODO: reset state as well as value
		this.fireEvent('financialdataupdated', this, this.getForm().items.map['os_resident_for_tax_purposes'], 1, false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'home_assignment';}}, this.home_assignment, false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 'pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, {isValid: function() {return true;}, getName: function(){return 's_pre_tax_super_mode';}}, 'auto', false);
		this.fireEvent('financialdataupdated', this, this.getForm().items.map['mfb_rate'], 2, false);
		this.fireEvent('financialdataupdated', this, this.getForm().items.map['s_mfb_rate'], 2, false);
		
		//clear the selected session in the combo
		this.getTopToolbar().items.map['session_combo'].clearValue();
		this.setSession(null);
		this.setSessionName(null);
		
		//load the session's internal transfers
		this.getComponent('internal_transfers_panel').resetInternalTransfers();
		
		//set the state to unsaved
		this.saved = false;
		
		//make sure the session is unlocked
		this.unlock();
		//console.info('resetForm: finished load defaults, ' + ((this.home_assignment) ? 'home ass': 'not home ass'));
	}
});
