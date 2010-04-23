
Ext.ns('TMN');

TMN.LastTMN = new Ext.Window({title: 'TMN - 2009', closable: false, width:418, height:436, resizable: false});

TMN.FinancialDetails = Ext.extend(Ext.form.FormPanel, {
	
	id: 'financial_details',
	frame: true,
	title: 'Financial Details',
	
	//custom parameters
	financial_data: {
		guid: 'testuserguid',
		spouse: 'testpartnerguid',
		session: 2,
		overseas: false,
		pre_tax_super_mode: 'auto',
		s_pre_tax_super_mode: 'auto',
		MFB_RATE: 2,
		S_MFB_RATE: 2
	},
	
	set_spouse: function(spouse){
		this.financial_data.spouse = spouse;
	},
	
	set_overseas: function(overseas){
		this.financial_data.overseas = overseas;
	},
	
	set_guid_session: function (guid, session){
		this.financial_data.guid = guid;
		this.financial_data.session = session;
		this.getComponent('internal_transfers').items.items[0].set_guid_session(guid,session);
	},
	
	set_financial_data: function (name, value){
		this.financial_data[name] = value;
	},
		
	initComponent: function() {
		
		var config = {
			monitorValid: true,
			
			items:[
			
			///////////////////////////////Taxable Income//////////////////////////////////////
			
				{
					itemId: 'taxable_income',
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
									itemId: 'net_stipend',
									name: 'NET_STIPEND',
									fieldLabel: 'Stipend',
									listeners: {
										change: this.updateCookie.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'This is the estimated amount that will go into your bank account each month.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
											});
										}
									}
								},
								{
									itemId: 'post_tax_super',
									name: 'POST_TAX_SUPER',
									fieldLabel: 'Post Tax Super',
									value: 0,
									listeners: {
										change: this.updateCookie.createDelegate(this),
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
										change: this.updateCookie.createDelegate(this),
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
									itemId: 'taxable_income_amount',
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
									toggleHandler: function (button, state){
										if (state == true){
											TMN.LastTMN.html = '<iframe src=\"http://mportal.ccca.org.au/TMN/php/tmn_2009.php?guid=' + this.guid + '\" height=400px width=400px></iframe>';
											TMN.LastTMN.show();
										} else {
											TMN.LastTMN.hide();
										}
									}.createDelegate(this),
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
									itemId: 's_net_stipend',
									name: 'S_NET_STIPEND',
									fieldLabel: 'Stipend',
									listeners: {
										change: this.updateCookie.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'This is the estimated amount that will go into your bank account each month.<br />This will be approximately half of your total Finanacial Package when you have included your MFB\'s.'
											});
										}
									}
								},
								{
									itemId: 's_post_tax_super',
									name: 'S_POST_TAX_SUPER',
									fieldLabel: 'Post Tax Super',
									value: 0,
									listeners: {
										change: this.updateCookie.createDelegate(this),
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
										change: this.updateCookie.createDelegate(this),
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
									itemId: 's_taxable_income_amount',
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
						} //eo spouse financial details
					]
				}, //eo taxable_income
				
			///////////////////////////////Housing//////////////////////////////////////
				
				{
					itemId: 'housing',
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
								change: this.updateCookie.createDelegate(this),
								render: function(c) {
									Ext.QuickTips.register({
										target: c.getEl(),
										text: 'Record the amount you would like to be paid from your Support Account each fortnight/month for housing.<br />Make sure your MFB or Stipend allows for this value.'
									});
								}
							}
						},
						{
							itemId: 'additional_housing_allowance',
							name: 'ADDITIONAL_HOUSING',
							readOnly: true,
							cls: 'x-form-readonly',
							fieldLabel: 'Additional Housing Allowance',
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
			                	select: function(combo, record, index) {
									this.updateCookie(combo,index,index);
			                	}.createDelegate(this)
			                }
						}
					]
				}, //eo housing
				
			///////////////////////////////MFB//////////////////////////////////////
				
				{
					itemId: 'mfb',
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
					                value: 'Full MFBs',
					                
					                store:new Ext.data.SimpleStore({
					                     fields:['mfbRateCode', 'mfbRateText'],
					                     data:[[0,'Zero MFBs'],[1,'Half MFBs'],[2,'Full MFBs']]
					                }),
					                displayField:'mfbRateText',
					                valueField:'mfbRateCode',
							                
					                listeners: {
					                	select: function(combo, record, index) {
											this.updateCookie(combo,index,index);
					                	}.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'Please select the MFB rate allowed for your role at CCCA.'
											});
										}
					                }
					            },
								{
									itemId: 'mfb',
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
					                value: 'Full MFBs',
					                
					                store:new Ext.data.SimpleStore({
					                     fields:['mfbRateCode', 'mfbRateText'],
					                     data:[[0,'Zero MFBs'],[1,'Half MFBs'],[2,'Full MFBs']]
					                }),
					                displayField:'mfbRateText',
					                valueField:'mfbRateCode',
							                
					                listeners: {
					                	select: function(combo, record, index) {
											this.updateCookie(combo,index,index);
					                	}.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'Please select the MFB rate allowed for your role at CCCA.'
											});
										}
					                }
					            },
								{
									itemId: 's_mfb',
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
								}
							]
						} //eo spouse mfb
					]
				}, //eo mfb
				
			///////////////////////////////Additional Extras//////////////////////////////////////
				
				{
					itemId: 'os_additional_extras',
					layout: 'form',
					title: 'Additional Extras',
					//collapsed: true,
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					defaults:{
						minValue: 0,
						value: 0
					},
					
					items: [
						{
							itemId: 'additional_extras',
							name: 'ADDITIONAL_EXTRAS',
							fieldLabel: 'Additional Extras',
							listeners: {
								change: this.updateCookie.createDelegate(this),
								render: function(c) {
									Ext.QuickTips.register({
										target: c.getEl(),
										text: 'These are your additional extras. Use this field to cover extraneous financial requirements.'
									});
								}
							}
						}
					]
				}, //eo additional extras
				
				///////////////////////////////Super//////////////////////////////////////
				{
					itemId: 'super',
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
									itemId: 'pre_tax_super',
									name: 'PRE_TAX_SUPER',
									cls: 'x-form-readonly',
									value: 0,
									fieldLabel: 'Pre Tax Super',
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
									scope: this,
									toggleHandler: function(button, state){
										//Button has been pressed so they are in manual mode
										if(state == true){
											//sets the mode
											this.financial_data.pre_tax_super_mode = 'manual';
											//removes readonly
											this.getForm().items.map['pre_tax_super'].purgeListeners();
											this.getForm().items.map['pre_tax_super'].removeClass('x-form-readonly');
											//starts updating on change
											this.getForm().items.map['pre_tax_super'].addListener('change', this.updateCookie, this);
										} else {
											//sets the mode
											this.financial_data.pre_tax_super_mode = 'auto';
											//stops it updating on change
											this.getForm().items.map['pre_tax_super'].purgeListeners();
											//makes it readonly
											this.getForm().items.map['pre_tax_super'].addClass('x-form-readonly');
											this.getForm().items.map['pre_tax_super'].addListener('focus', function(field){field.blur();});
											//set it to the auto value
											this.updateCookie(this.getForm().items.map['pre_tax_super'], this.getForm().items.map['pre_tax_super'].getValue(), this.getForm().items.map['pre_tax_super'].getValue());
										}
									}
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
					            	itemId: 'ioof',
					           		xtype: 'combo',
					           		fieldLabel: 'Is your super fund IOOF?',
					           		name: 'IOOF',
					            	hiddenName: 'IOOF',
					            	hiddenId: 'IOOF_hidden',
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
					                	//when index 1, "No", is selected show life cover amount
					                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
					                	select: function(combo, record, index) {
											if (index == 1) {
												combo.nextSibling().expand();
											} else {
												combo.nextSibling().collapse();
												Ext.Msg.alert('Super Fund Change!', 'If you are changing your Super Fund you need to fill out <a href="pdf/superfund_change.pdf" target="_blank">this</a> form for the change to apply.');
											}
											this.updateCookie(combo, index, index);
					                	}.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'Select whether you have nominated IOOF for your super fund or not.<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a super nomination form.</b>'
											});
										}
					                }
					            },
					            {
					            	xtype: 'panel',
					            	layout: 'form',
					            	//collapsed: true,
					            	items: [
					            		{
							            	itemId: 'life_cover_amount',
							           		xtype: 'combo',
							           		fieldLabel: 'Life Cover Amount',
							           		name: 'LIFE_COVER',
							            	hiddenName: 'LIFE_COVER',
							            	hiddenId: 'LIFE_COVER_hidden',
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
							                	select: function(combo, record, index) {
							                		Ext.Msg.alert('Life Cover Change!', 'If you are changing your Life Cover you need to fill out <a href="pdf/ioof_lifecover_change.zip" target="_blank">this</a> form for the change to apply.');
													this.updateCookie(combo,index,index);
							                	}.createDelegate(this),
												render: function(c) {
													Ext.QuickTips.register({
														target: c.getEl(),
														text: 'CCCA will pay $1/week. If you would like additional Life Cover (on top of the $1/week) record the total amount (including the $1/week to a maximum of $10/week).<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a Life Cover Change form.</b>'
													});
												}
							                }
							            },
							            {
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
							                	select: function(combo, record, index) {
							                		Ext.Msg.alert('INCOME PROTECTION Cover Change!', '<b>You have changed where your INCOME PROTECTION Cover is taken from.<br /><br />Note:<br />This cover is <i>DIFFERENT</i> to your Life Cover.<br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!</b>');
													this.updateCookie(combo,index,index);
							                	}.createDelegate(this),
												render: function(c) {
													Ext.QuickTips.register({
														target: c.getEl(),
														text: 'Nominate where your Income Protection Cover should be taken from.<br /><b>This cover is <i>DIFFERENT</i> to your Life Cover.</b><br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!'
													});
												}
							                }
							            }
					            	]	
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
									itemId: 's_pre_tax_super',
									name: 'S_PRE_TAX_SUPER',
									cls: 'x-form-readonly',
									fieldLabel: 'Pre Tax Super',
									value: 0,
									listeners: {
										change: this.updateCookie.createDelegate(this),
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
									scope: this,
									toggleHandler: function(button, state){
										//Button has been pressed so they are in manual mode
										if(state == true){
											//sets the mode
											this.financial_data.s_pre_tax_super_mode = 'manual';
											//removes readonly
											this.getForm().items.map['s_pre_tax_super'].purgeListeners();
											this.getForm().items.map['s_pre_tax_super'].removeClass('x-form-readonly');
											//starts updating on change
											this.getForm().items.map['s_pre_tax_super'].addListener('change', this.updateCookie, this);
										} else {
											//sets the mode
											this.financial_data.s_pre_tax_super_mode = 'auto';
											//stops it updating on change
											this.getForm().items.map['s_pre_tax_super'].purgeListeners();
											//makes it readonly
											this.getForm().items.map['s_pre_tax_super'].addClass('x-form-readonly');
											this.getForm().items.map['s_pre_tax_super'].addListener('focus', function(field){field.blur();});
											//set it to the auto value
											this.updateCookie(this.getForm().items.map['s_pre_tax_super'], this.getForm().items.map['s_pre_tax_super'].getValue(), this.getForm().items.map['s_pre_tax_super'].getValue());
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
					            	itemId: 's_ioof',
					           		xtype: 'combo',
					           		fieldLabel: 'Is your super fund IOOF?',
					           		name: 'S_IOOF',
					            	hiddenName: 'S_IOOF',
					            	hiddenId: 'S_IOOF_hidden',
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
					                	//when index 1, "No", is selected show life cover amount
					                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
					                	select: function(combo, record, index) {
											if (index == 1) {
												combo.nextSibling().expand();
											} else {
												combo.nextSibling().collapse();
												Ext.Msg.alert('Super Fund Change!', 'If you are changing your Super Fund you need to fill out <a href="pdf/superfund_change.pdf" target="_blank">this</a> form for the change to apply.');
											}
											this.updateCookie(combo, index, index);
					                	}.createDelegate(this),
										render: function(c) {
											Ext.QuickTips.register({
												target: c.getEl(),
												text: 'Select whether you have nominated IOOF for your super fund or not.<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a super nomination form.</b>'
											});
										}
					                }
					            },
					            {
					            	xtype: 'panel',
					            	layout: 'form',
					            	//collapsed: true,
					            	items: [
					            		{
							            	itemId: 's_life_cover_amount',
							           		xtype: 'combo',
							           		fieldLabel: 'Life Cover Amount',
							           		name: 'S_LIFE_COVER',
							            	hiddenName: 'S_LIFE_COVER',
							            	hiddenId: 'S_LIFE_COVER_hidden',
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
							                	select: function(combo, record, index) {
													Ext.Msg.alert('Life Cover Change!', 'If you are changing your Life Cover you need to fill out <a href="pdf/ioof_lifecover_change.zip" target="_blank">this</a> form for the change to apply.');
													this.updateCookie(combo,index,index);
							                	}.createDelegate(this),
												render: function(c) {
													Ext.QuickTips.register({
														target: c.getEl(),
														text: 'CCCA will pay $1/week. If you would like additional Life Cover (on top of the $1/week) record the total amount (including the $1/week to a maximum of $10/week).<br /><b>If you select differently to your previous TMN form, the changes will <i>NOT</i> be automatic: you must contact Member Care and submit a Life Cover Change form.</b>'
													});
												}
							                }
							            },
							            {
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
							                	select: function(combo, record, index) {
							                		Ext.Msg.alert('INCOME PROTECTION Cover Change!', '<b>You have changed where your INCOME PROTECTION Cover is taken from.<br /><br />Note:<br />This cover is <i>DIFFERENT</i> to your Life Cover.<br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!</b>');
													this.updateCookie(combo,index,index);
							                	}.createDelegate(this),
												render: function(c) {
													Ext.QuickTips.register({
														target: c.getEl(),
														text: 'Nominate where your Income Protection Cover should be taken from.<br /><b>This cover is <i>DIFFERENT</i> to your Life Cover.</b><br />Click <a href="https://www.mygcx.org/CCCAMemberPortal/file/225/Insurance_calc.xls" target="_blank">here</a> to download Income Protection Cover calculator.<br />Your Income Protection Cover is taken out annually and can be quite a large amount and can have a <i>significant</i> impact on the account it comes from.<br />So choose carefully!'
													});
												}
							                }
							            }
					            	]	
					            }
								
							]
						} //eo spouse super
					]
				}, //eo super
				
			///////////////////////////////MMR//////////////////////////////////////
				
				{
					itemId: 'mmr',
					layout: 'column',
					//title: 'MFB',
					//collapsed: true,
					defaults: {
						defaultType: 'numberfield',
						bodyStyle: 'padding:10px',
						defaults:{
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
										change: this.updateCookie.createDelegate(this),
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
									fieldLabel: 'MMR',
									listeners: {
										change: this.updateCookie.createDelegate(this),
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
					itemId: 'international_donations',
					layout: 'form',
					title: 'Incoming International Donations',
					//collapsed: true,
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					labelWidth: 300,
					defaults:{
						minValue: 0,
						value: 0
					},
					
					items: [
						{
							itemId: 'internation_donations',
							name: 'INTERNATIONAL_DONATIONS',
							fieldLabel: 'Incoming International Donations',
							listeners: {
								change: this.updateCookie.createDelegate(this),
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
				
				{
					itemId: 'internal_transfers',
					layout: 'form',
					title: 'Internal Tranfers',
					//collapsed: true,
					defaultType: 'numberfield',
					bodyStyle: 'padding:10px',
					
					items: [
						TMN.Grid
					]
				} //eo internal transfers
				
			], // eo form items
			
				///////////////////////////////Validation Bar//////////////////////////////////////
			
			bbar: new Ext.ux.StatusBar({
				defaultText: 'Ready',
				plugins: new Ext.ux.ValidationStatus({form: this.id}) //change test_form to id from above (ln 6)
			})
		};
		
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		TMN.FinancialDetails.superclass.initComponent.apply(this, arguments); // change Form to match above name (ln 4)
	}, //eo initForm
	
				///////////////////////////////Loading the Form//////////////////////////////////////
	
	//required function do not edit unless you know what you are doing
	loadForm: function (successCallback, failureCallback) {
		//hide stuff that shouldnt be seen
		if (this.financial_data.spouse == false){
			//hide taxable income spouse
			var fieldset = this.getComponent('taxable_income');
			fieldset.getComponent('spouse').hide();
			fieldset.getComponent('my').columnWidth = 1;
			
			//hide mfb spouse
			fieldset = this.getComponent('mfb');
			fieldset.getComponent('spouse').hide();
			fieldset.getComponent('my').columnWidth = 1;
			
			//hide super details spouse
			fieldset = this.getComponent('super');
			fieldset.getComponent('spouse').hide();
			fieldset.getComponent('my').columnWidth = 1;
			
			//hide mmr spouse
			fieldset = this.getComponent('mmr');
			fieldset.getComponent('spouse').hide();
			fieldset.getComponent('my').columnWidth = 1;
			
			this.form.items.each(function (item, index, length) {
				if (item.getItemId().substr(0,2) == 's_'){
					item.disable();
				}
			});
			
			this.doLayout();
		} else {
			//show taxable income spouse
			var fieldset = this.getComponent('taxable_income');
			fieldset.getComponent('my').columnWidth = 0.5;
			fieldset.getComponent('spouse').show();
			
			//hide mfb spouse
			fieldset = this.getComponent('mfb');
			fieldset.getComponent('my').columnWidth = 0.5;
			fieldset.getComponent('spouse').show();
			
			//hide super details spouse
			fieldset = this.getComponent('super');
			fieldset.getComponent('my').columnWidth = 0.5;
			fieldset.getComponent('spouse').show();
			
			//hide mmr spouse
			fieldset = this.getComponent('mmr');
			fieldset.getComponent('my').columnWidth = 0.5;
			fieldset.getComponent('spouse').show();
			
			this.form.items.each(function (item, index, length) {
				if (item.getItemId().substr(0,2) == 's_'){
					item.enable();
				}
			});
			
			this.doLayout();
		}
		this.getComponent('mfb').collapse(false);
		if (!this.financial_data.overseas) this.getComponent('os_additional_extras').hide();
		//////////////////////////////////////
					//set partner, session and guid here (in both FD and Grid)
			
		//////////////////////////////////////
		
		//load grid
		this.getComponent('internal_transfers').items.items[0].getStore().load({
			params: {mode: 'get', session: this.financial_data.session}
		});
		
		//load fields
		this.load({
			url: 'php/load_financial_details.php',
			success: function(form, action) {
				if (successCallback !== undefined) successCallback();
			},
			failure: function(form, action) {
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
				}
				if (failureCallback !== undefined) failureCallback();
			}
		})
	},
	
				///////////////////////////////Submiting the form//////////////////////////////////////
	
	//required function do not edit unless you know what you are doing
	submitForm: function (successCallback, failureCallback) {
		
		//hide last TMN window
		TMN.LastTMN.hide();
		
		//submit fields
		this.form.submit({
			url: 'php/submit_financial_details.php',
			params: {guid: this.financial_data.guid, session: this.financial_data.session},
			success: function (form, action) {
				this.response = action.response.responseText;
				if (successCallback !== undefined) successCallback();
			}.createDelegate(this),
			failure: function (form, action) {
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
				}
				if (failureCallback !== undefined) failureCallback();
			}
		});
	},
	
				///////////////////////////////Cookie Code//////////////////////////////////////
	
	updateCookie: function(field, newVal, oldVal){
		if (field.isValid())
		{
			this.set_financial_data(field.getName(), newVal);
			
			Ext.Ajax.request({
				url: 'php/cookie_monster.php',
				params: {financial_data: JSON.stringify(this.financial_data)},
				success: this.cookieHandler.createDelegate(this),
				failure: function(){Ext.MessageBox.alert('Server Error', 'Server Could Not Calculate Values! Please Contact The Technology Team at tech.team@ccca.org.au');}
			});
		}
	},
	
	cookieHandler: function(response, options){
		
		//update the financial data with the updated values from the BE
		var return_object = JSON.parse(response.responseText);
		
		//check if it returned data or errrors
		if (return_object.success == true || return_object.success == 'true'){
			delete (this.financial_data);
			this.financial_data = return_object.financial_data;
			
			var s_mfb_range = false;
			
			//check for spouse calculated values
			if (this.financial_data.spouse != false){
				
				if (this.financial_data.S_TAXABLE_INCOME !== undefined){
					this.getComponent('taxable_income').getComponent('spouse').getComponent('s_tax').setValue(this.financial_data.S_TAX);
					this.getComponent('taxable_income').getComponent('spouse').getComponent('s_taxable_income_amount').setValue(this.financial_data.S_TAXABLE_INCOME);
				}
				
				if (this.financial_data.S_MAX_MFB !== undefined){
					this.getComponent('mfb').getComponent('spouse').getComponent('s_mfb').setValue(this.financial_data.S_MAX_MFB);
					s_mfb_range = true;
				}
				
				if (this.financial_data.S_PRE_TAX_SUPER !== undefined){
					this.getComponent('super').getComponent('spouse').getComponent('s_pre_tax_super').setValue(this.financial_data.S_PRE_TAX_SUPER);
				}
				
				if (this.financial_data.S_EMPLOYER_SUPER !== undefined){
					this.getComponent('super').getComponent('spouse').getComponent('s_employer_super').setValue(this.financial_data.S_EMPLOYER_SUPER);
				}
				
			} else {
				s_mfb_range = true;
			}
			
			//checks for my calculated values
			if (this.financial_data.TAXABLE_INCOME !== undefined){
				this.getComponent('taxable_income').getComponent('my').getComponent('tax').setValue(this.financial_data.TAX);
				this.getComponent('taxable_income').getComponent('my').getComponent('taxable_income_amount').setValue(this.financial_data.TAXABLE_INCOME);
			}
			
			if (this.financial_data.MAX_MFB !== undefined && s_mfb_range){
				this.getComponent('mfb').getComponent('my').getComponent('mfb').setValue(this.financial_data.MAX_MFB);
				this.getComponent('mfb').expand(true);
				//update label with range
			}
			
			if (this.financial_data.ADDITIONAL_HOUSING !== undefined){
				this.getComponent('housing').getComponent('additional_housing_allowance').setValue(this.financial_data.ADDITIONAL_HOUSING);
			}
			
			if (this.financial_data.PRE_TAX_SUPER !== undefined){
					this.getComponent('super').getComponent('my').getComponent('pre_tax_super').setValue(this.financial_data.PRE_TAX_SUPER);
			}
			
			if (this.financial_data.EMPLOYER_SUPER !== undefined){
				this.getComponent('super').getComponent('my').getComponent('employer_super').setValue(this.financial_data.EMPLOYER_SUPER);
			}
		} else {
			if (return_object.errors['S_NET_STIPEND'] !== undefined)
				this.getForm().items.map['s_net_stipend'].markInvalid(return_object.errors['S_NET_STIPEND']);
			
			if (return_object.errors['NET_STIPEND'] !== undefined)
				this.getForm().items.map['net_stipend'].markInvalid(return_object.errors['NET_STIPEND']);
		}
		
	}
	
}); //eo extend

Ext.reg('financialdetailsform', TMN.FinancialDetails);

