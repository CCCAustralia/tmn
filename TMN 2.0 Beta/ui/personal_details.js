

Ext.ns('TMN');

TMN.PersonalDetails = Ext.extend(Ext.FormPanel, {	
	
	id: 'personal_details',
	
	//class wide variables definitions
	guid: '',
	session: '',
	spouse: '',
	overseas:'',
	
	get_spouse: function() {return this.spouse},
	
	get_overseas: function() {return this.overseas},
	
	set_guid_session: function (guid, session){
		this.guid = guid;
		this.session = session;
	},
	
	personal_details_fs: new Ext.form.FieldSet({
		title: 'My Personal Details',
		columnWidth:.5,
		labelWidth: 240,
		bodyStyle:'padding:10px',
		defaultType: 'textfield',
		defaults:{width:150},
			
		items: [{
				itemId: 'first',
                fieldLabel: 'First Name',
                name: 'FIRSTNAME',
                readOnly: true,
                allowBlank:false,
				listeners: {
					focus: function(field)	{field.blur();}
				}
            },{
                fieldLabel: 'Last Name',
                name: 'SURNAME',
                readOnly: true,
				listeners: {
					focus: function(field)	{field.blur();}
				}
            },{
			    itemId: 'ministry',
            	xtype: 'combo',
			    fieldLabel: 'Ministry',
			    valueField: 'MINISTRY_ID',
			    hiddenName: 'MINISTRY',
			    hiddenId: 'MINISTRY',
			    displayField: 'MINISTRY_ID',
			    triggerAction: 'all',
            	editable: false,
			    forceSelection: true,
			    allowBlank:false,
			    
			    mode: 'remote',
			    // store getting items from server
			    store: new Ext.data.JsonStore({
			         itemId:'ministry_store',
			         root: 'Ministry',
			        fields:['MINISTRY_ID', 'MINISTRY_LEVY'],
			        url:'php/combofill.php',
			        baseParams: {
			        	mode: 'Ministry'
			        }
			    })
			},{
            	itemId: 'ftptos',
            	xtype: 'combo',
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
                     data:[[0,'Full Time'],[1,'Part Time']/*,[2,'Overseas']*/]
                }),
                displayField:'ftptosName',
                valueField:'ftptosCode',
                listeners: {
                	//when index 1, "Part Time", is selected enable daysperwk, otherwise disable it
                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
                	select: function(combo, record, index) {
						if (index == 1) {
				/***** In Next Version Make them disappear *****/
							combo.nextSibling().setDisabled(false);
						} else {
							combo.nextSibling().reset();
							combo.nextSibling().setDisabled(true);
						}
                	}
                }
            },{
            	itemId: 'daysperwk',
           		xtype: 'combo',
           		fieldLabel: 'How many days per week do you work?',
           		name: 'DAYS_PER_WEEK',
            	hiddenName: 'DAYS_PER_WEEK',
            	hiddenId: 'DAYS_PER_WEEK_hidden',
           		triggerAction:'all',
           		disabled:true,
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
	
	}),
	
	s_personal_details_fs: new Ext.form.FieldSet({
		title: 'Spouse Personal Details',
		columnWidth:.5,
		labelWidth: 240,
		bodyStyle:'padding:10px',
		defaultType: 'textfield',
		defaults:{width:150},
		
		items: [{
				itemId: 's_first',
                fieldLabel: 'First Name',
                name: 'S_FIRSTNAME'
            },{
            	itemId: 's_last',
                fieldLabel: 'Last Name',
                name: 'S_SURNAME'
            },{
			    itemId: 's_ministry',
            	xtype: 'combo',
			    fieldLabel: 'Ministry',
			    valueField: 'MINISTRY_ID',
			    hiddenName: 'S_MINISTRY',
			    hiddenId: 'S_MINISTRY',
			    displayField: 'MINISTRY_ID',
			    triggerAction: 'all',
            	editable: false,
			    forceSelection: true,
			    
			    mode: 'remote',
			    // store getting items from server
			    store:new Ext.data.JsonStore({
			         itemId:'ministry_store',
			         root: 'Ministry',
			        fields:['MINISTRY_ID', 'MINISTRY_LEVY'],
			        url:'php/combofill.php',
			        baseParams: {
			        	mode: 'Ministry'
			        }
			    })
			},{
            	itemId: 's_ftptos',
            	xtype: 'combo',
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
                     data:[[0,'Full Time'],[1,'Part Time']/*,[2,'Overseas']*/]
                }),
                displayField:'ftptosName',
                valueField:'ftptosCode',
                listeners: {
                	//when index 1, "Part Time", is selected enable daysperwk, otherwise disable it
                	//make sure that when the field  is loaded (found at personal_details>listeners>afterRender>this.load) that it does this check too
                	select: function(combo, record, index) {
						if (index == 1) {
				/***** In Next Version Make them disappear *****/
							combo.nextSibling().setDisabled(false);
						} else {
							combo.nextSibling().reset();
							combo.nextSibling().setDisabled(true);
						}
                	}
                }
            },{
            	itemId: 's_daysperwk',
           		xtype: 'combo',
           		fieldLabel: 'How many days per week do you work?',
           		name: 'S_DAYS_PER_WEEK',
            	hiddenName: 'S_DAYS_PER_WEEK',
            	hiddenId: 'S_DAYS_PER_WEEK_hidden',
           		triggerAction:'all',
           		disabled:true,
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
	
	}),
	
	mpd_details_fs: new Ext.form.FieldSet({
		title: 'MPD Details',
		columnWidth:1,
		labelWidth: 240,
		bodyStyle:'padding:10px',
		defaultType: 'textfield',
		defaults:{width:150},
			
		items: [{
            	itemId: 'mpd',
           		xtype: 'combo',
           		fieldLabel: 'Are you currently doing MPD?',
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
						if (index == 1) {
				/***** In Next Version Make them disappear *****/
							combo.nextSibling().setDisabled(false);
							combo.nextSibling().nextSibling().setDisabled(false);
						} else {
							combo.nextSibling().reset();
							combo.nextSibling().nextSibling().reset();
							combo.nextSibling().setDisabled(true);
							combo.nextSibling().nextSibling().setDisabled(true);
						}
                	}
                }
            },{
            	itemId: 'mpd_supervisor_first',
            	fieldLabel: 'MPD Supervisor First Name',
            	name: 'M_FIRSTNAME',
            	allowBlank: false,
            	disabled: true
            },{
            	itemId: 'mpd_supervisor_last',
            	fieldLabel: 'MPD Supervisor Last Name',
            	name: 'M_SURNAME',
            	allowBlank: false,
            	disabled: true
            }
		]
	}),
	
	initComponent: function() {
	
		var config = {
			itemId: 'personal_details',
			frame:true,
			buttonAlign: 'right',
			title: 'Personal Details',
			layout: 'column',
			trackResetOnLoad: true,
			defaultType: 'textfield',
			
			items: [this.personal_details_fs, this.s_personal_details_fs, this.mpd_details_fs]
		
		}; //eo config
		
		
		//apply config to the initialConfig of FormPanel
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		
		//call parent
		TMN.PersonalDetails.superclass.initComponent.apply(this, arguments);
	
		
	}, //eo initComponent
	
	loadForm: function (successCallback, failureCallback) {
		
		this.load({
			url:'php/personal_details.php',
			waitMsgTarget: true,
			waitMsg: 'Loading',
			params:{mode:'get', guid: this.guid},
			success: function (form, action) {
				//if success check if the disabled fields need to be enabled
	            if (this.personal_details_fs.getComponent('ftptos').getValue() == 1){
	            	this.personal_details_fs.getComponent('daysperwk').setDisabled(false);
				}
				if (this.s_personal_details_fs.getComponent('s_ftptos').getValue() == 1){
	            	this.s_personal_details_fs.getComponent('s_daysperwk').setDisabled(false);
				}
				if (this.mpd_details_fs.getComponent('mpd').getValue() == 1){
	            	this.mpd_details_fs.getComponent('mpd_supervisor_first').setDisabled(false);
	            	this.mpd_details_fs.getComponent('mpd_supervisor_last').setDisabled(false);
				}
				
				if (successCallback !== undefined) successCallback();
			}.createDelegate(this), //eo success
			failure: function (form, action){
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
					
				}
				
				if (action.failureType === Ext.form.Action.LOAD_FAILURE) {
					Ext.MessageBox.alert('Database Error', 'You could not be found in our Database! You are not allowed to do a TMN! Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a> if you think you should be able to.'/*, function(){Ext.getBody().mask(); window.location = "https://www.mygcx.org/";}*/);
				}
				
				if (failureCallback !== undefined) failureCallback();
			}
		})
	},
	
	submitForm: function(successCallback, failureCallback) {
		this.form.submit({
			url: 'php/personal_details.php',
			params:{mode:'set', guid: this.guid},
			success: function (form, action) {
				//does this person have a spouse
				if(form.items.map['s_first'].getValue() != '' ){
					this.spouse = true;
				} else {
					this.spouse = false;
				}
				
				if(form.items.map['ftptos'].getValue() == 2 ){
					this.overseas = true;
				} else {
					this.overseas = false;
				}
				
				//pass spouse to financial details
				Ext.getCmp('financial_details').set_spouse(this.spouse);
				//pass overseas to financial details
				Ext.getCmp('financial_details').set_overseas(this.overseas);
				
				if (successCallback !== undefined) successCallback();
			}.createDelegate(this),
			failure: function (form, action) {
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
				} else if (action.failureType === Ext.form.Action.SERVER_INVALID) {
					if (action.result.errors.S_FIRSTNAME !== undefined) {
							Ext.MessageBox.alert('Spouse Error', action.result.errors.S_FIRSTNAME);
					}
					if (action.result.errors.M_FIRSTNAME !== undefined) {
							Ext.MessageBox.alert('MPD Supervisor Error', action.result.errors.M_FIRSTNAME);
					}
				}
				
				if (failureCallback !== undefined) failureCallback();
			}
		});
	}
	
}); //eo TMN.PersonalDetails