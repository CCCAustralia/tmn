
Ext.ns('tmn', 'tmn.view');		//the namespace of the project

/**
 * @class		tmn.view.PersonalDetailsForm
 * 
 * <p>
 * <b>Description:</b> The Form that collects a missionaries Personal Details.
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
tmn.view.PersonalDetailsForm = function(view, config) {
	/**
	 * @cfg {Object}	view			The object that defines the container that holds this form
	 * @note To be able to use this property you must pass it to the constructor when you create an instance of this class.
	 */
	this.view = view || {};					//the view that this form is contained in
	//class wide variables definitions
	//set config variable to passed or default
	config = config || {};
	
	//holds the data for the minsitry combo
	this.ministry_combo_store = new Ext.data.JsonStore({
        itemId:'ministry_store',
        root: 'Ministry',
        fields:['MINISTRY_ID', 'MINISTRY_LEVY'],
        url:'php/imp/combofill.php',
        autoLoad: {
        	params: {mode: 'Ministry'}
        }
    });
	
	//set config options to passed or default
	/**
	 * @cfg {String}	id				The id parameter of the html tag that contains the form.<br />
	 * 									Default: 'personal_details_form'
	 */
	this.id					=	config.id || 'personal_details_form';
	/**
	 * @cfg {String}	title			The title displayed in the header of the form.<br />
	 * 									Default: 'Personal Details'
	 */
	this.title				=	config.title || 'Personal Details';
	/**
	 * @cfg {String}	load_url		The url of the server script that will process the forms load request.<br />
	 * 									Default: 'php/personal_details.php'
	 */
	this.load_url			=	config.load_url || 'php/personal_details.php';
	/**
	 * @cfg {String}	submit_url		The url of the server script that will process the forms submit request.<br />
	 * 									Default: 'php/personal_details.php'
	 */
	this.submit_url			=	config.submit_url || 'php/personal_details.php';
	/**
	 * @cfg {String}	aussie_form		Defines what type of user this form is intended for.
	 * 									If a user is not an Aussie Based Missionary they will not be able to view this form<br />
	 * 									Default: true
	 */
	this.aussie_form;
	/**
	 * @cfg {String}	overseas_form	Defines what type of user this form is intended for.
	 * 									If a user is not an Overseas Missionary they will not be able to view this form<br />
	 * 									Default: true
	 */
	this.overseas_form;
	(config.aussie_form === undefined) ? this.aussie_form = true : this.aussie_form = config.aussie_form;
	(config.overseas_form === undefined) ? this.overseas_form = true : this.overseas_form = config.overseas_form;

	/**
	 * The users marital status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users marital status use the methods provided by this class.
	 */
	this.spouse= '';
	/**
	 * The users overseas status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users overseas status use the methods provided by this class.
	 */
	this.overseas='';
	
	//register events
	this.addEvents(
		/**
         * @event single
         * Fires once the php back-end has varified that the user is single. That happens in {@link #onSubmitSuccess}.
         */
		'single',
		/**
         * @event married
         * Fires once the php back-end has varified that the user is married. That happens in {@link #onSubmitSuccess}.
         */
		'married',
		/**
         * @event overseas
         * Fires when the user selects that they serve Overseas.
         * @param {Ext.form.FormPanel}	this		A reference to the form that called it (ie send it this)
		 * @param {boolean} spouse 					A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
         */
		'overseas',
		/**
         * @event aussie
         * Fires when the user selects that they serve in australia (ie Full Time or Part Time).
         * @param {Ext.form.FormPanel}	this		A reference to the form that called it (ie send it this)
		 * @param {boolean} spouse 					A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
         */
		'aussie',
		/**
         * @event mpdyes
         * Fires when the user selects that they are in Ministry Partner Development.
         * @param {Ext.form.FormPanel}	this 		A reference to the form that called it (ie send it this)
         */
		'mpdyes',
		/**
         * @event mpdno
         * Fires when the user selects that they are not in Ministry Partner Development.
         * @param {Ext.form.FormPanel}	this		A reference to the form that called it (ie send it this)
         */
		'mpdno',
		/**
         * @event fulltime
         * Fires when the user selects that they serve Full Time.
         * @param {Ext.form.FormPanel}	this		A reference to the form that called it (ie send it this)
		 * @param {boolean} spouse 					A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
         */
		'fulltime',
		/**
         * @event parttime
         * Fires when the user selects that they serve Part Time.
         * @param {Ext.form.FormPanel}	this		A reference to the form that called it (ie send it this)
		 * @param {boolean} spouse 					A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
         */
		'parttime',
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
		 * @param {Ext.form.BasicForm}	form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
		 * @param {Ext.form.Action}		action 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
         */
		'submitsuccess',
		/**
         * @event submitfailure
         * Fires when a form's submit ajax request is a failure.
		 * @param {Ext.form.FormPanel} this 		A reference to the form that called it (ie send it this)
		 * @param {Ext.form.BasicForm} form 		The Object that represents just the form (see {@link Ext.form.BasicForm})
		 * @param {Ext.form.Action} action 			The action Object created from the ajax repsonse (see {@link Ext.form.Action})
         */
		'submitfailure'
	);
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	this.config =  {
			id: this.id,
			frame:true,
			buttonAlign: 'right',
			title: this.title,
			layout: 'column',
			defaults: {
				defaultType: 'textfield'
			},
			
			items: [
			///////////////////////User's Personal Details//////////////////////
				{
					itemId: 'personal_details',
					title: 'My Personal Details',
					layout: 'form',
					columnWidth:.5,
					labelWidth: 240,
					bodyStyle:'padding:10px',	//creates a 10px space around the panel's contents
					defaultType: 'textfield',
						
					items: [{
							itemId: 'first',
				            fieldLabel: 'First Name',
				            name: 'FIRSTNAME',
				            readOnly: true,
				            allowBlank:false,
				       		width: 150,
							listeners: {
								focus: function(field)	{field.blur();}		//bluring when on focus makes sure the user can't edit the field
							}
				        },{
				            fieldLabel: 'Last Name',
				            name: 'SURNAME',
				            readOnly: true,
				            allowBlank:false,
				       		width: 150,
							listeners: {
								focus: function(field)	{field.blur();}
							}
				        },{
						    itemId: 'ministry',
				        	xtype: 'combo',
				       		width: 150,
						    fieldLabel: 'Ministry',
						    valueField: 'MINISTRY_ID',
						    hiddenName: 'MINISTRY',
						    hiddenId: 'MINISTRY',
						    displayField: 'MINISTRY_ID',
						    triggerAction: 'all',
				        	editable: false,
						    forceSelection: true,
						    allowBlank:false,
						    
						    mode: 'local',
						    // store getting items from server
						    store: this.ministry_combo_store
						},{
				        	itemId: 'ftptos',
				        	xtype: 'combo',
				       		width: 150,
				        	fieldLabel: 'Are you Full Time, Part Time Or Overseas?',
				        	name: 'FT_PT_OS',
				        	hiddenName: 'FT_PT_OS',
				        	hiddenId: 'FT_PT_OS_hidden',
				        	value: 0,
				        	triggerAction:'all',
				        	editable: false,
				            mode:'local',
				            store:new Ext.data.SimpleStore({
				                 fields:['ftptosCode', 'ftptosName'],
				                 data:[[0,'Full Time'],[1,'Part Time'],[2,'Overseas']]
				            }),
				            displayField:'ftptosName',
				            valueField:'ftptosCode',
				            listeners: {
				            	//when index 1, "Part Time", is selected enable daysperwk, otherwise disable it
				            	select: function(combo, record, index) {
									switch (index) {
										case 0:											//fire events for Full Time
											this.fireEvent('fulltime', this, false);
											break;
										case 1:											//fire events for Part Time
											this.fireEvent('parttime', this, false);
											break;
										case 2:											//fire events for Overseas
											this.fireEvent('overseas', this, false);
											break;
									}
				            	},
				            	scope: this
				            }
				        },
				        {
				        	itemId: 'days_per_week',
				        	xtype: 'panel',
				        	layout: 'form',
				        	labelWidth: 240,
				        	defaults: {width:150},
				        	items:[
					        	{
						        	itemId: 'daysperwk',
						       		xtype: 'combo',
						       		fieldLabel: 'How many days per week do you work?',
						       		name: 'DAYS_PER_WEEK',
						        	hiddenName: 'DAYS_PER_WEEK',
						        	hiddenId: 'DAYS_PER_WEEK_hidden',
						       		triggerAction:'all',
						       		emptyText: 'Enter Days Per Week...',
						       		validationEvent: 'blur',
						       		allowBlank: false,
						        	editable:false,
						            mode:'local',
						            store:new Ext.data.SimpleStore({
						                 fields:['daysperwkCode', 'daysperwkName'],
						                 data:[[0,'One Day per Week'],[1,'Two Days per Week'],[2,'Three Days per Week'],[3,'Four Days per Week']]
						            }),
						            displayField:'daysperwkName',
						            valueField:'daysperwkCode'
					        	}
					        ]
				        }
				    ]
				
				},
			///////////////////////Spouse's Personal Details//////////////////////
				{
					itemId: 's_personal_details',
					title: 'Spouse Personal Details',
					layout: 'form',
					columnWidth:.5,
					labelWidth: 240,
					bodyStyle:'padding:10px',
					defaultType: 'textfield',
					
					items: [{
							itemId: 's_first',
				            fieldLabel: 'First Name',
				            name: 'S_FIRSTNAME',
				       		width: 150
				        },{
				        	itemId: 's_last',
				            fieldLabel: 'Last Name',
				            name: 'S_SURNAME',
					       	width: 150
				        },{
						    itemId: 's_ministry',
				        	xtype: 'combo',
				       		width: 150,
						    fieldLabel: 'Ministry',
						    valueField: 'MINISTRY_ID',
						    hiddenName: 'S_MINISTRY',
						    hiddenId: 'S_MINISTRY',
						    displayField: 'MINISTRY_ID',
						    triggerAction: 'all',
				        	editable: false,
						    forceSelection: true,
						    
						    mode: 'local',
						    // store getting items from server
						    store:this.ministry_combo_store
						},{
				        	itemId: 's_ftptos',
				        	xtype: 'combo',
				       		width: 150,
				        	fieldLabel: 'Are you Full Time, Part Time Or Overseas?',
				        	name: 'S_FT_PT_OS',
				        	hiddenName: 'S_FT_PT_OS',
				        	hiddenId: 'S_FT_PT_OS_hidden',
				        	value: 0,
				        	triggerAction:'all',
				        	editable:false,
				            mode:'local',
				            store:new Ext.data.SimpleStore({
				                 fields:['ftptosCode', 'ftptosName'],
				                 data:[[0,'Full Time'],[1,'Part Time'],[2,'Overseas']]
				            }),
				            displayField:'ftptosName',
				            valueField:'ftptosCode',
				            listeners: {
				            	//when index 1, "Part Time", is selected enable daysperwk, otherwise disable it
				            	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
				            	select: function(combo, record, index) {
									switch (index) {
										case 0:											//fire events for Full Time
											this.fireEvent('fulltime', this, true);
											break;
										case 1:											//fire events for Part Time
											this.fireEvent('parttime', this, true);
											break;
										case 2:											//fire events for Overseas
											this.fireEvent('overseas', this, true);
											break;
									}
				            	},
				            	scope: this
				            }
				        },
				        {
				        	itemId: 's_days_per_week',
				        	xtype: 'panel',
				        	layout: 'form',
				        	labelWidth: 240,
				        	defaults: {width:150},
				        	items:[
								{
						        	itemId: 's_daysperwk',
						       		xtype: 'combo',
						       		fieldLabel: 'How many days per week do you work?',
						       		name: 'S_DAYS_PER_WEEK',
						        	hiddenName: 'S_DAYS_PER_WEEK',
						        	hiddenId: 'S_DAYS_PER_WEEK_hidden',
						       		triggerAction:'all',
						       		emptyText: 'Enter Days Per Week...',
						       		validationEvent: 'blur',
						        	editable:false,
						            mode:'local',
						            
						            store:new Ext.data.SimpleStore({
						                 fields:['daysperwkCode', 'daysperwkName'],
						                 data:[[0,'One Day per Week'],[1,'Two Days per Week'],[2,'Three Days per Week'],[3,'Four Days per Week']]
						            }),
						            displayField:'daysperwkName',
						            valueField:'daysperwkCode'
						        }
							]
				        }
				    ]
				
				},
			///////////////////////Ministry Partner Development Details//////////////////////
				{
					itemId: 'mpd_details',
					title: 'MPD Details',
					layout: 'form',
					columnWidth:1,
					labelWidth: 240,
					bodyStyle:'padding:10px',
					defaultType: 'textfield',
						
					items: [
					    {
				        	itemId: 'mpd',
				       		xtype: 'combo',
				       		width: 150,
				       		fieldLabel: 'Are you an NMA or currently doing MPD?',
				       		name: 'MPD',
				        	hiddenName: 'MPD',
				        	hiddenId: 'MPD_hidden',
				       		triggerAction:'all',
				       		emptyText: 'Enter Yes or No...',
				       		validationEvent: 'blur',
				       		allowBlank: false,
				        	editable:false,
				            mode:'local',
				            
				            store:new Ext.data.SimpleStore({
				                 fields:['mpdCode', 'mpdName'],
				                 data:[[0,'No'],[1,'Yes']]
				            }),
				            displayField:'mpdName',
				            valueField:'mpdCode',
				            
				            listeners: {
				            	//when index 1, "No", is selected enable mpd_supervisor, otherwise disable it
				            	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
				            	select: function(combo, record, index) {
					    			switch (index) {
										case 0:
											this.fireEvent('mpdno', this);
											break;
										case 1:
											this.fireEvent('mpdyes', this);
											break;
									}
				            	},
					    		scope: this
				            }
				        },
				        {
				        	itemId: 'mpd_supervisor',
				        	xtype: 'panel',
				        	layout: 'form',
				        	labelWidth: 240,
				        	defaultType: 'textfield',
				        	defaults: {width:150},
				        	items: [
						        {
						        	itemId: 'mpd_supervisor_first',
						        	fieldLabel: 'MPD Supervisor First Name',
						        	name: 'M_FIRSTNAME',
						        	allowBlank: false
						        },{
						        	itemId: 'mpd_supervisor_last',
						        	fieldLabel: 'MPD Supervisor Last Name',
						        	name: 'M_SURNAME',
						        	allowBlank: false
						        }
						    ]
				        }
					]
				}
			]
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.PersonalDetailsForm.superclass.constructor.call(this, this.config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.PersonalDetailsForm, Ext.FormPanel, {
	
	/**
	 * Returns whether the user has a spouse or not.
	 * @returns {boolean}						A boolean that tell you if the user has a spouse.
	 */
	hasSpouse: function() {
		if (this.spouse == '') {	//if this.spouse holds the default value it means the user's marital status hasn't been confirmed
									//so return whether the user thinks they have a spouse (ie the user has entered a value for their spouse's first name or last name)
			return (this.getForm().items.map['s_first'].getValue() != '' || this.getForm().items.map['s_last'].getValue() != '');
		} else {
			return this.spouse;		//if the marital status has been confirmed then return that.
		}
	},
	
	/**
	 * Lets this form know if the user has a spouse or not.
	 * @param {boolean}				spouse		A boolean that tell you if the user has a spouse.
	 */
	setSpouse: function(spouse) {this.spouse = spouse;},
	
	/**
	 * Returns whether the user serves overseas or not.
	 * @returns {boolean}						A boolean that tell you if the user serves overseas.
	 */
	isOverseas: function() {return this.overseas;},
	
	/**
	 * Lets this form know if the user serves overseas or not.
	 * @param {boolean}				overseas	A boolean that tell you if the user serves overseas.
	 */
	setOverseas: function(overseas) {this.overseas = overseas;},
	
	/**
	 * Returns the user's Full Time/Part Time/Overseas status.
	 * @returns	{string}						A string that represents the FTPTOS status of the user. (can only be 'Full Time', 'Part Time' or 'Overseas')
	 */
	getFtptosValue: function() {
		switch (this.getForm().items.map['ftptos'].getValue()) {
			case 0:
				return 'Full Time';	//return string equivilant of combo's index
			case 1:
				return 'Part Time';
			case 2:
				return 'Overseas';
			default:
				return '';
		}
	},
	
	/**
	 * Returns the spouse's Full Time/Part Time/Overseas status.
	 * @returns	{string}						A string that represents the FTPTOS status of the spouse. (can only be 'Full Time', 'Part Time' or 'Overseas')
	 */
	getSpouseFtptosValue: function() {
		switch (this.getForm().items.map['s_ftptos'].getValue()) {
			case 0:
				return 'Full Time';	//return string equivilant of combo's index
			case 1:
				return 'Part Time';
			case 2:
				return 'Overseas';
			default:
				return '';
		}
	},
	
	/**
	 * Gets the User's FTPTOS status and sets the spouse's FTPTOS status to that value.
	 */
	copyFtptosValue: function() {
		this.getForm().items.map['s_ftptos'].setValue(this.getForm().items.map['ftptos'].getValue());
	},

	/**
	 * Gets the Spouse's FTPTOS status and sets the user's FTPTOS status to that value.
	 */
	copySpouseFtptosValue: function() {
		this.getForm().items.map['ftptos'].setValue(this.getForm().items.map['s_ftptos'].getValue());
	},
	
	/**
	 * Will hide/show & disable/enable the mpd supervisor fields
	 * @param {boolean}				visable		A boolean that lets the function know whether to hide or show the fields.
	 */
	setMpdSupervisorVisable: function(visable) {
		if (visable) {
			this.getForm().items.map['mpd_supervisor_first'].setDisabled(false);
			this.getForm().items.map['mpd_supervisor_last'].setDisabled(false);
			this.getComponent('mpd_details').getComponent('mpd_supervisor').show();
		} else {
			this.getComponent('mpd_details').getComponent('mpd_supervisor').hide();
			this.getForm().items.map['mpd_supervisor_first'].reset();
			this.getForm().items.map['mpd_supervisor_last'].reset();
			this.getForm().items.map['mpd_supervisor_first'].setDisabled(true);
			this.getForm().items.map['mpd_supervisor_last'].setDisabled(true);
		}
	},
	
	/**
	 * Will hide/show & disable/enable the days per week fields
	 * @param {boolean}				visable		A boolean that lets the function know whether to hide or show the fields.
	 */
	setDaysPerWeekVisable: function(make_visable) {
		if (make_visable) {
			this.getForm().items.map['daysperwk'].setDisabled(false);
			this.getComponent('personal_details').getComponent('days_per_week').show();
		} else {
			this.getComponent('personal_details').getComponent('days_per_week').hide();
			this.getForm().items.map['daysperwk'].reset();
			this.getForm().items.map['daysperwk'].setDisabled(true);
		}
	},
	
	/**
	 * Will hide/show & disable/enable the spouse's days per week fields
	 * @param {boolean}				visable		A boolean that lets the function know whether to hide or show the fields.
	 */
	setSpouseDaysPerWeekVisable: function(make_visable) {
		if (make_visable) {
			this.getForm().items.map['s_daysperwk'].setDisabled(false);
			this.getComponent('s_personal_details').getComponent('s_days_per_week').show();
		} else {
			this.getComponent('s_personal_details').getComponent('s_days_per_week').hide();
			this.getForm().items.map['s_daysperwk'].reset();
			this.getForm().items.map['s_daysperwk'].setDisabled(true);
		}
	},
	
	/**
	 * Will load the form with an ajax request to php/personal_details.php.
	 * It will fire the loadsuccess or loadfailure when the ajax request returns.
	 */
	loadForm: function () {
		
		this.load({
			url: this.load_url,
			waitMsgTarget: true,
			waitMsg: 'Loading',
			params:{mode:'get'},
			scope: this,
			success: function (form, action) {
				this.fireEvent('loadsuccess', this, form, action);
			}, //eo success
			failure: function (form, action){
				this.fireEvent('loadfailure', this, form, action);
			}
		});
	},
	
	/**
	 * Will correct type problems from sending the data (ie string to int). It also triggers any events needed based on the values returned.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that succeeded (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadSuccess: function (form, action) {
		//convert any strings to numbers (ie "0" to 0)
		for (fieldCount = 0; fieldCount < form.items.length; fieldCount++){
			if ( !isNaN(parseInt(form.items.items[fieldCount].getValue())) )
				form.items.items[fieldCount].setValue(parseInt(form.items.items[fieldCount].getValue()));
		}
		
		//if success check if any fields need to be hidden or shown
        switch (form.items.map['ftptos'].getValue()) {
			case 0:											//fire events for Full Time
				this.fireEvent('fulltime', this, false);
				break;
			case 1:											//fire events for Part Time
				this.fireEvent('parttime', this, false);
				break;
			case 2:											//fire events for Overseas
				this.fireEvent('overseas', this, false);
				break;
		}
        	
        switch (form.items.map['s_ftptos'].getValue()) {
			case 0:											//fire events for Spouse Full Time
				this.fireEvent('fulltime', this, true);
				break;
			case 1:											//fire events for Spouse Part Time
				this.fireEvent('parttime', this, true);
				break;
			case 2:											//fire events for Spouse Overseas
				this.fireEvent('overseas', this, true);
				break;
		}
        
		switch (form.items.map['mpd'].getValue()) {
			case 0:											//fire events for MPD No
				this.fireEvent('mpdno', this);
				break;
			case 1:											//fire events for MPD Yes
				this.fireEvent('mpdyes', this);
				break;
		}
	},
	
	/**
	 * Will display error messages based on the error that caused the failure.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onLoadFailure: function (form, action){
		if (action.failureType === Ext.form.Action.CONNECT_FAILURE)
			Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
		
		if (action.failureType === Ext.form.Action.LOAD_FAILURE)
			Ext.MessageBox.alert('Database Error', 'You could not be found in our Database! You are not allowed to do a TMN! Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a> if you think you should be able to.', function(){Ext.getBody().mask(); window.location = "http://mportal.ccca.org.au/";});
	},
	
	/**
	 * Will submit the form with an ajax request to php/personal_details.php.
	 * It sends the request with all the enabled field's values.
	 * It will fire the submitsuccess or submitfailure when the ajax request returns.
	 */
	submitForm: function() {
		this.form.submit({
			url: this.submit_url,
			params:{mode:'set'},
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
	 * Will fire the appropriate marrital status event based on what the php returned.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitSuccess: function (form, action) {
		//does this person have a spouse (checks the spouse name after the php has succeeded, ie the php has varified the marrital status)
		if( form.items.map['s_first'].getValue() != '' && form.items.map['s_last'].getValue() != '') {
			this.fireEvent('married');
		} else {
			this.fireEvent('single');
		}
	},
	
	/**
	 * Will display error messages based on the error that caused the failure.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitFailure: function (form, action){
		if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
			Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
		} else if (action.failureType === Ext.form.Action.SERVER_INVALID) {
			if (action.result.errors.S_FIRSTNAME !== undefined)
					Ext.MessageBox.alert('Spouse Error', action.result.errors.S_FIRSTNAME);
					
			if (action.result.errors.M_FIRSTNAME !== undefined)
					Ext.MessageBox.alert('MPD Supervisor Error', action.result.errors.M_FIRSTNAME);
		}
	}
});
