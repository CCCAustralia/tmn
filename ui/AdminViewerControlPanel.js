
Ext.ns('tmn', 'tmn.view');

/**
 * @class		AuthorisationPanel
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
tmn.view.AuthorisationViewerControlPanel = function(view, config) {
	/**
	 * @cfg {Object}	view			The object that defines the container that holds this form
	 * @note To be able to use this property you must pass it to the constructor when you create an instance of this class.
	 */
	this.view	= view		|| {};					//the view that this form is contained in
	//set config variable to passed or default
	config		= config	|| {};
	
	//G_SESSION is a global variable set in a script tag on the html page (droped in by the php file)
	//if the url has a session in it (G_SESSION holds the session sent in the url)
	this.session= G_SESSION;
	
	this.addEvents(
	
			/**
	         * @event selectsession
	         * Fires when the user clicks the next button.
	         */
			'selectsession',
			
			/**
	         * @event resetviewer
	         * Fires when the user clicks the next button.
	         */
			'resetviewer',
			
			/**
	         * @event display
	         * Fires when the user clicks the next button.
	         */
			'display'
	);

	//defines a store for storing sessions
	this.sessionStore	= new Ext.data.JsonStore({
        itemId:		'session_store',
        root:		'data',
        fields:		['SESSION_ID', 'SESSION_NAME', 'FIRSTNAME', 'SURNAME', 'FAN', 'DATE_MODIFIED'],
        sortInfo:	{
        	field:		'SURNAME',
            direction:	'ASC'
        },
        url:		'./tmn-adminviewer.php',
        autoLoad:	{
        	params: { mode: 'load' }
        },
        listeners: {
        	scope:	this,
        	load:	function(store, records, options) {
        		//select the session once the session combo has rendered
        		if (this.getSession() > 0) {
        			if (!this.rendered) {        				
        				this.on('afterrender', this.loadUrlSession, {controller:this, session:this.getSession()});
        			} else {
        				this.loadUrlSession.call({controller:this, session: this.getSession()}, this);
        			}
        		}
        	}
        }
    });
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
		title:		'Controls',
		frame:		true,
		layout:		'column',
		bodyStyle:	'padding:5px 5px 0px 5px',
		items: [
		        
			{
				layout: 'form',
				columnWidth:1,
				items: [
					{
					    id:				'session_combo',
			        	xtype:			'combo',
			        	width:			650,
					    fieldLabel:		'Approved Sessions',
					    hiddenName:		'SESSION',
					    hiddenId:		'SESSION_hidden',
					    triggerAction:	'all',
			        	editable:		false,
					    forceSelection:	true,
					    allowBlank:		false,
					    
					    mode:			'local',
					    // store getting items from server
					    store:			this.sessionStore,
					    
					    valueField:		'SESSION_ID',
						displayField:	'SURNAME',
					    tpl:			'<tpl for=\".\"><div class=\"x-combo-list-item\">{SURNAME}, {FIRSTNAME} - {FAN} - {DATE_MODIFIED}</div></tpl>',
					    listeners:		{
					    	scope:	this,
					    	select: function(combo, record, index) {
					    		this.fireEvent('selectsession', combo, record, index);
					    	}
					    }
					}
				]
			},
			{
				layout: 'form',
	        	labelWidth:	75,
				columnWidth:.2,
				items: [
					{
					    id:			'reminder_round_one',
			        	xtype:		'button',
					    text:		'Send Round One Reminders',
					    scope:		this,
					    handler:	function(button, event) {

					    	button.disable();
					    	
					    	Ext.Ajax.request({
								url: './php/admin/adminprocessor.php',
								params: {action: 'reminder_round_one'},
								success: this.buttonHandler,
								failure: this.buttonHandler,
								scope: this
							});
					    	
					    },
					    listeners:	{
					    	scope: this,
					    	render: function(button) {
					    		Ext.QuickTips.register({
									target: button.getEl(),
									text: 'Sends emails to Missionaries with TMN\'s that are unsubmitted or awaiting approval. You will be emailed a list of everyone who was emailed.'
								});
					    	}
					    }
					}
				]
			},
            {
                layout: 'form',
                labelWidth:	75,
                columnWidth:.2,
                items: [
                    {
                        id:			'reminder_round_two',
                        xtype:		'button',
                        text:		'Send Round Two Reminders',
                        scope:		this,
                        handler:	function(button, event) {

                            button.disable();

                            Ext.Ajax.request({
                                url: './php/admin/adminprocessor.php',
                                params: {action: 'reminder_round_two'},
                                success: this.buttonHandler,
                                failure: this.buttonHandler,
                                scope: this
                            });

                        },
                        listeners:	{
                            scope: this,
                            render: function(button) {
                                Ext.QuickTips.register({
                                    target: button.getEl(),
                                    text: 'Sends emails to Missionaries with TMN\'s that are unsubmitted or awaiting approval. It also cc\'s their first approver. You will be emailed a list of everyone who was emailed.'
                                });
                            }
                        }
                    }
                ]
            },
            {
                layout: 'form',
                labelWidth:	75,
                columnWidth:.2,
                items: [
                    {
                        id:			'reminder_round_three',
                        xtype:		'button',
                        text:		'Send Round Three Reminders',
                        scope:		this,
                        handler:	function(button, event) {

                            button.disable();

                            Ext.Ajax.request({
                                url: './php/admin/adminprocessor.php',
                                params: {action: 'reminder_round_three'},
                                success: this.buttonHandler,
                                failure: this.buttonHandler,
                                scope: this
                            });

                        },
                        listeners:	{
                            scope: this,
                            render: function(button) {
                                Ext.QuickTips.register({
                                    target: button.getEl(),
                                    text: 'Sends emails to Missionaries with TMN\'s that are unsubmitted or awaiting approval. It also cc\'s their first and second approvers. You will be emailed a list of everyone who was emailed.'
                                });
                            }
                        }
                    }
                ]
            },
            {
                layout: 'form',
                labelWidth:	75,
                columnWidth:.2,
                items: [
                    {
                        id:			'reminder_round_four',
                        xtype:		'button',
                        text:		'Send Round Four Reminders',
                        scope:		this,
                        handler:	function(button, event) {

                            button.disable();

                            Ext.Ajax.request({
                                url: './php/admin/adminprocessor.php',
                                params: {action: 'reminder_round_four'},
                                success: this.buttonHandler,
                                failure: this.buttonHandler,
                                scope: this
                            });

                        },
                        listeners:	{
                            scope: this,
                            render: function(button) {
                                Ext.QuickTips.register({
                                    target: button.getEl(),
                                    text: 'Sends emails to Missionaries with TMN\'s that are unsubmitted or awaiting approval. It also cc\'s all three approvers. You will be emailed a list of everyone who was emailed.'
                                });
                            }
                        }
                    }
                ]
            },
			{
				layout: 'form',
	        	labelWidth:	75,
				columnWidth:.2,
				items: [
					{
					    id:			'low_account',
			        	xtype:		'button',
					    text:		'Run Low Account Process',
					    scope:		this,
					    handler:	function(button, event) {
					    	
					    	button.disable();
					    	
					    	Ext.Ajax.request({
								url: './php/admin/adminprocessor.php',
								params: {action: 'low_account'},
								success: this.buttonHandler,
								failure: this.buttonHandler,
								scope: this
							});
					    	
					    },
					    listeners:	{
					    	scope: this,
					    	render: function(button) {
					    		Ext.QuickTips.register({
									target: button.getEl(),
									text: 'Runs Low Account Process. Sending emails to everyone who needs to be notified. You will be cced on all emails.'
								});
					    	}
					    }
					}
				]
			},
			{
				  xtype: 'box',
				  columnWidth:1,
				  autoEl:	{
					  			tag: 'center',
					  			html: '<div id="tmn-authviewer-overall-status" class=""><span id="tmn-authviewer-overall-status-label">Approval Trail: </span><span id="tmn-authviewer-overall-status-status" style="color:#999999;">n/a</span></div>'
					  		}
			}
			
		]
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.AuthorisationViewerControlPanel.superclass.constructor.call(this, config);
};

Ext.extend(tmn.view.AuthorisationViewerControlPanel, Ext.form.FormPanel, {
	
	getSession: function() {
		return this.session;
	},
	
	setSession: function(session) {
		this.session	= session;
	},
	
	buttonHandler: function(response, options) {

		var return_object	= Ext.util.JSON.decode(response.responseText),
			returnMsg		= return_object.message;
		
		if (return_object.success === true) {
			
			this.showButtonResult(Ext.MessageBox.INFO, "Done!", returnMsg);
			
		} else {
			
			this.showButtonResult(Ext.MessageBox.ERROR, "Error!", returnMsg);

		}
	},
	
	showButtonResult: function(icon, title, message) {
		
		Ext.MessageBox.show({
			icon: icon,
			buttons: Ext.MessageBox.OK,
			closable: false,
			title: title,
			msg: message
		});

        if (icon === Ext.MessageBox.ERROR) {
            Ext.getCmp('reminder_round_one').enable();
            Ext.getCmp('reminder_round_two').enable();
            Ext.getCmp('reminder_round_three').enable();
            Ext.getCmp('reminder_round_four').enable();
            Ext.getCmp('low_account').enable();
        }
		
	},
	
	fail: function() {
		Ext.MessageBox.show({
			icon: Ext.MessageBox.ERROR,
			buttons: Ext.MessageBox.OK,
			closable: false,
			title: 'Error!',
			msg: 'There was an error processing your request. Please try again.'
		});
		
		this.fireEvent('resetviewer');
	},

	//needs to be called on after render so needs to be called like so this.loadUrlSession.call({controller:this, session: this.getSession()}, this)
	loadUrlSession: function(form) {

		var sessionRecordIndex	= this.controller.sessionStore.find('SESSION_ID', this.controller.getSession()),
		combo					= form.getForm().items.map['session_combo'],
		sessionRecord;

		//if the session is found load it
		if (sessionRecordIndex >= 0) {
			//grab record
			sessionRecord		= this.controller.sessionStore.getAt(sessionRecordIndex);
			//set the combo to the right value
			form.getForm().items.map['session_combo'].setValue(sessionRecord.get('SESSION_NAME'));
			//select and load the session using the data just grabbed
			this.controller.selectSession(combo, sessionRecord, sessionRecordIndex);
		//if session not found tell user
		} else {
			Ext.MessageBox.show({
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.MessageBox.OK,
				closable: false,
				title: 'Error!',
				msg: 'The session specified in the link was not found. Please select another one from drop down list. '
					+ 'If the session you are looking for isn\'t in the drop down please email '
					+ '<a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>.'
			});
		}
	},
	
	selectSession: function(combo, record, index) {
		
		this.session	= record.get('SESSION_ID');
		
		Ext.Ajax.request({
			url: './php/admin/adminviewer.php',
			scope: this,
			params: {
				mode: 'get',
				session: record.get('SESSION_ID')
			},
			success: function(response, options) {
				this.fireEvent('display', response, options);
			},
			failure: this.fail
		});
	},
	
	processSession: function(progress) {
		////Approval Trail
		
		var statusEl	= Ext.get("tmn-authviewer-overall-status-status"),
			htmlString	= '';
		
		//create html from response
		for (level in progress) {
			if (progress[level].total > 0) {
				//Authorisers "approve"
				htmlString += '<br />Approved';
			}
			if (progress[level].name == 'Finance') {
				//Finance "processes"
				htmlString += '<br />Processed';
			}
			
			 htmlString += ' by <a href=\"mailto:' + progress[level].email + '\">' + progress[level].name + '</a> - ' + progress[level].date;
		}
		
		//set overall status color
		statusEl.setStyle('color', "#336600");
		statusEl.update(htmlString);
	},
	
	resetControls:	function() {
		Ext.getCmp('session_combo').clearValue();
		Ext.get('tmn-authviewer-overall-status-status').setStyle('color', "#999999");
		Ext.get('tmn-authviewer-overall-status-status').update('n/a');
	}
	
});
