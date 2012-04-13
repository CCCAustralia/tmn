
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
tmn.view.AuthorisationPanel = function(view, config) {
	/**
	 * @cfg {Object}	view			The object that defines the container that holds this form
	 * @note To be able to use this property you must pass it to the constructor when you create an instance of this class.
	 */
	this.view	= view		|| {};					//the view that this form is contained in
	//set config variable to passed or default
	config		= config	|| {};

	//set config options to passed or default
	/**
	 * @cfg {String}	id				The id parameter of the html tag that contains the form.<br />
	 * 									Default: 'print_form'
	 */
	this.id					=	config.id		|| 'authorisation_panel';
	
	this.title				=	config.title 	|| 'Authorisation Level';
	
	this.leader				=	config.leader	|| 'Your';
	
	this.mode				=	config.mode		|| 'all';

	this.noNames			=	config.noNames	|| false;
	
	this.user_id			=	0;
	
	

	if (!this.noNames) {
		//holds the data for the name combo
		this.nameStore			= new Ext.data.JsonStore({
	        itemId:		'name_store',
	        root:		'data',
	        url:		'php/imp/namefill.php',
	        fields:		['ID', 'FIRSTNAME', 'SURNAME', 'MINISTRY'],
	        baseParams:	{mode: this.mode},
	        autoLoad:	true,
	        listeners:	{
	        	scope:	this,
	        	load:	function(store, records, options) {
	        		//if there is only one record
	        		if (records.length == 1) {
	        			
	        			//set the user_id to this record
	        			this.user_id	= records[0].data.ID;
	        			
	        			if (this.rendered) {
		        			//set the name fields to the contents of that record
				    		//and disable those fields
		        			this.autoSelectName.call(records[0], this);	//this.autoSelectNamecall(scope, param1)
	        			} else {
	        				this.on('afterrender', this.autoSelectName, records[0]);
	        			}
	        		}
	        	}
	        }
	    });
		
		/*	
		//holds the test data for the name combo
		this.nameStore			= new Ext.data.ArrayStore({
	        itemId:	'name_store',
	        fields:	['FIRSTNAME', 'SURNAME', 'MINISTRY'],
	        data:	[['Michael', 'Harrison', 'StudentLife'],
	               ['Tom', 'Flynn', 'StudentLife'],
	               ['Kent', 'Keller', 'StudentLife']
	        ]
	    });
		*/ 
	}

 
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
			itemId:		this.id,
			frame:		true,
			//title:		this.title + " - " + this.leader,
			header:		false,
			bodyStyle:	'padding:0',
			items:		[
				 {
					 xtype:	'label',
					 text:	'Enter the name of your ' + this.leader + ' Authoriser:',
					 style:	{
				         marginBottom: '10px'
				     }
				 },
			     {
					 itemId:		'name',
			    	 xtype:			'compositefield',
			    	 msgTarget:		'side',
                     fieldLabel:	'Full Name',
			    	 items: [
						{
							itemId:			'first_name',
							xtype:			'combo',
							flex:			1,
							fieldLabel:		'First Name',
							name:			'FIRSTNAME',
							hiddenName:		'FIRSTNAME',
							hiddenId:		'FIRSTNAME_hidden',
						    emptyText:		'Authoriser Name...',
						    triggerAction:	'all',
				            allowBlank:		false,
						    hideTrigger:	false,
							editable: 		false,
							forceSelection:	true,
						    mode:			'local',
						    store:			this.nameStore,
						    tpl:			'<tpl for="."><div class="x-combo-list-item">{FIRSTNAME} {SURNAME} - {MINISTRY}</div></tpl>',
						    displayField:	'FIRSTNAME',
						    valueField:		'FIRSTNAME',
						    listeners: {
						    	select: function(combo, record, index) {
						    		this.user_id	= record.data.ID;
						    		/*
						    		var compositefield = this.getForm().items.map['name'];
						    		compositefield.items.each(function(item, index, length){
						    			if (item.getItemId() == 'last_name') {
						    				item.setValue(this.data.SURNAME);
						    			}
						    		}, record);
						    		*/
						    	},
						    	scope: this
						    }
						}/*,
						{
							itemId:			'last_name',
							xtype:			'combo',
							flex:			1,
							fieldLabel:		'Last Name',
							name:			'SURNAME',
							hiddenName:		'SURNAME',
							hiddenId:		'SURNAME_hidden',
						    emptyText:		'Last Name...',
				            allowBlank:		false,
						    hideTrigger:	false,
							typeAhead:		true,
							editable: 		true,
							forceSelection:	true,
						    mode:			'local',
						    store:			this.nameStore,
						    tpl:			'<tpl for="."><div class="x-combo-list-item">{FIRSTNAME} {SURNAME} - {MINISTRY}</div></tpl>',
						    displayField:	'SURNAME',
						    valueField:		'SURNAME',
						    listeners: {
						    	select: function(combo, record, index) {
						    		this.user_id	= record.data.ID;
						    		var compositefield = this.getForm().items.map['name'];
						    		compositefield.items.each(function(item, index, length){
						    			if (item.getItemId() == 'first_name') {
						    				item.setValue(this.data.FIRSTNAME);
						    			}
						    		}, record);
						    	},
						    	scope: this
						    }
						}*/
			    	 ]
			     }
			     
				
			]
	};
	
	//if the noNames config is true then delete all the stuff associated with the names
	if (this.noNames) {
		config	= {
				itemId:		this.id,
				frame:		true,
				header:		false,
				bodyStyle:	'padding:0'
		};
		this.nameStore	= null;
	}
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.AuthorisationPanel.superclass.constructor.call(this, config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.AuthorisationPanel, Ext.form.FormPanel, {
	
	isValid: function() {
		
		var compositefield,
			invalidIndex	= -1;
		
		if (!this.noNames) {
			//find the first invalid field
			compositefield	= this.getForm().items.map['name'];
			
			if (!this.hidden) {
				invalidIndex = compositefield.items.findIndexBy(function(item, key){
					if (item.getValue() == '' || item.getValue() == null || item.getValue() === undefined) {
						return true;
					}
				});
			}
		}
		
		//if the index is less than zero no invalid fields were found so return true
		if (invalidIndex < 0) {
			return true;
		} else {
			return false;
		}
	},
	
	/**
	 * Needs to have showPanel Called first or it will return with no reasons.
	 */
	getData: function() {
		
		var compositefield,
			returnObj		= {};
		
		if (!this.noNames) {
			compositefield	= this.getForm().items.map['name'];
		
			returnObj['user_id']		= this.user_id;
		}
		
		//grab the reason array
		returnObj['reasons']	= {};
		
		if (this.reasonArray !== undefined) {
			returnObj['reasons']	= this.reasonArray;
		}
		
		return returnObj;
		
	},
	
	resetFields: function() {
		if (!this.noNames) {
			this.user_id		= 0;
			
			var compositefield	= this.getForm().items.map['name'];
			compositefield.items.each(function(item, index, length){
				item.clearValue();
				item.clearInvalid();
			}, this);
		}
	},
	
	/**
	 * Will take the only record left as the scope and this form as its first parameter and
	 * will set the name fields values, the user_id and will disable the name fields.
	 * 
	 * Note: Must be called with the scope being the only record in the store.
	 * Use this.autoSelectName.call(records[0], this) to do that
	 */
	autoSelectName: function(form) {
		if (!this.noNames) {
			form.user_id		= this.data.ID;
			
			var compositefield	= form.getForm().items.map['name'];
			compositefield.items.each(function(item, index, length){
				item.setValue(this.data[item.getName()]);
				item.disable();
			}, this);
		}
	},
	
	showPanel: function(reasonArray) {
		
		var tmnAuthContainer	= Ext.get('tmn-' + this.id + '-authorisation-div'),
			recordArray;
		
		if (!this.noNames) {
			
			recordArray	= this.nameStore.getRange();
			
			//if there is only one record select it and disable fields
			if (recordArray.length == 1) {
				this.autoSelectName.call(recordArray[0], this); //this.autoSelectNamecall(scope, param1)
			} else {
				this.resetFields();				
			}
		}
		
		
		this.show();
		
		this.reasonArray		= reasonArray;
		
		//if this tag has already been added then remove it before adding the new one
		if (tmnAuthContainer != null) {
			tmnAuthContainer.remove();
		}

		//if this is a aussie based session
		if (reasonArray['aussie-based'] !== undefined) {
			
			//make sure there are reasons to output
			if (reasonArray['aussie-based']['reasons'].length > 0) {
				var tpl = new Ext.XTemplate(
							'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
								'<div class="tmn-authorisation">',
									'<div class="header">Reasons for Needing ' + this.leader + ' Authorisation</div>',
									'<tpl for="reasons"><div class="reason">- {reason}</div></tpl>',
								'</div>',
							'</div>'
						);
				
				tpl.append(this.body, reasonArray['aussie-based']);
			}
			
		//if this is an international based session
			//TODO: change to if international assignment
		} else {

			var el	= null;
			
			//if there are international reasons append them
			if (reasonArray['international-assignment']['reasons'].length > 0) {
				var tpl = new Ext.XTemplate(
					'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
						'<div class="tmn-authorisation">',
							'<div class="header">International Assignment - Reasons for Needing ' + this.leader + ' Authorisation</div>',
							'<tpl for="reasons"><div class="reason">- {reason}</div></tpl>',
						'</div>',
					'</div>'
				);
		
				el = tpl.append(this.body, reasonArray['international-assignment'], true);
			}
			
			//if there are home assignment reasons
			if (reasonArray['home-assignment']['reasons'].length > 0) {
				
				var home_tpl;
				
				// and if no international reasons were added add the container to the body as well other wise
				//add a break and append the home reasons
				if (el == null) {
					el = this.body;
					
					home_tpl = new Ext.XTemplate(
							'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
								'<div class="tmn-authorisation">',
									'<div class="header">Home Assignment - Reasons for Needing ' + this.leader + ' Authorisation</div>',
									'<tpl for="reasons"><div class="reason">- {reason}</div></tpl>',
								'</div>',
							'</div>'
						);
					
				//if there were international reasons added and there are home assignment ones to be added as well
				//append them to the tmn-authorisation tag
				} else {
					home_tpl = new Ext.XTemplate(
							'<p>&nbsp;</p>',
							'<div class="tmn-authorisation">',
								'<div class="header">Home Assignment - Reasons for Needing ' + this.leader + ' Authorisation</div>',
								'<tpl for="reasons"><div class="reason">- {reason}</div></tpl>',
							'</div>'
						);
				}
		
				home_tpl.append(el, reasonArray['home-assignment']);
			}
			
		}
	},
	
	resetPanel: function() {
		var tmnAuthContainer	= Ext.get('tmn-' + this.id + '-authorisation-div');
		
		//if this tag has already been added then remove it before adding the new one
		if (tmnAuthContainer != null) {
			tmnAuthContainer.remove();
		}
		
		if (this.reasonArray !== undefined) {
			delete this.reasonArray;
		}
		
		this.resetFields();
	},
	
	hidePanel: function() {
		
		this.resetFields();
		
		this.hide();
	}
});
