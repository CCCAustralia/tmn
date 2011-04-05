
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
	this.view	= view || {};					//the view that this form is contained in
	//set config variable to passed or default
	config		= config || {};

	//set config options to passed or default
	/**
	 * @cfg {String}	id				The id parameter of the html tag that contains the form.<br />
	 * 									Default: 'print_form'
	 */
	this.id					=	config.id || 'authorisation_panel';
	
	this.title				=	config.title || 'Authorisation Level';
	
	this.mode				=	config.mode || 'all';
	//holds the data for the name combo
	this.nameStore			= new Ext.data.ArrayStore({
        itemId:	'name_store',
        fields:	['FIRSTNAME', 'SURNAME', 'MINISTRY'],
        data:	[['Michael', 'Harrison', 'StudentLife'],
               ['Tom', 'Flynn', 'StudentLife'],
               ['Kent', 'Keller', 'StudentLife']
        ]
        //autoLoad: {
        //	params: {mode: this.mode}
        //}
    });
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
			itemId:		this.id,
			frame:		true,
			title:		this.title,
			bodyStyle:	'padding:0',
			items:		[
			     {
			    	xtype:	'label',
			    	text:	'Enter the name of the person who needs to Authorise this for you:',
					style:	{
		                marginBottom: '10px'
		            }
			     },
				{
					itemId:			'first_name',
					xtype:			'combo',
					width:			150,
					fieldLabel:		'First Name',
					name:			'FIRSTNAME',
					hiddenName:		'FIRSTNAME',
					hiddenId:		'FIRSTNAME_hidden',
				    emptyText:		'First Name...',
				    hideTrigger:	true,
					typeAhead:		true,
					editable: 		true,
					forceSelection:	true,
				    mode:			'local',
				    store:			this.nameStore,
				    tpl:			'<tpl for="."><div class="x-combo-list-item">{FIRSTNAME} {SURNAME} - {MINISTRY}</div></tpl>',
				    displayField:	'FIRSTNAME',
				    valueField:		'FIRSTNAME',
				    listeners: {
				    	select: function(combo, record, index) {
				    		console.info(record.data.SURNAME);
				    		this.getForm().items.map['last_name'].setValue(record.data.SURNAME);
				    		console.info(this.getForm().items.map['last_name'].getValue());
				    	},
				    	scope: this
				    }
				},
				{
					itemId:			'last_name',
					xtype:			'combo',
					width:			150,
					fieldLabel:		'Last Name',
					name:			'SURNAME',
					hiddenName:		'SURNAME',
					hiddenId:		'SURNAME_hidden',
				    emptyText:		'Last Name...',
				    hideTrigger:	true,
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
				    		console.info(record.data.FIRSTNAME);
				    		this.getForm().items.map['first_name'].setValue(record.data.FIRSTNAME);
				    		console.info(this.getForm().items.map['first_name'].getValue());
				    	},
				    	scope: this
				    }
				}
			]
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.AuthorisationPanel.superclass.constructor.call(this, config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.AuthorisationPanel, Ext.form.FormPanel, {
	
	isValid: function() {
		if (this.getForm().items.map['first_name'].isValid() && this.getForm().items.map['last_name'].isValid()) {
			return true;
		} else {
			return false;
		}
	},
	
	showPanel: function(reasonArray) {
		this.show();
		
		var tmnAuthContainer	= Ext.get('tmn-' + this.id + '-authorisation-div');
		
		//if this tag has already been added then remove it before adding the new one
		if (tmnAuthContainer != null) {
			tmnAuthContainer.remove();
		}
		
		//create the config object for the 
		if (reasonArray['aussie-based'] !== undefined) {
			
			var tpl = new Ext.XTemplate(
						'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
							'<div class="tmn-authorisation">',
								'<div class="header">Reasons for needing Authorisation</div>',
								'<tpl for="reasons"><div class="indent">- {reason}</div></tpl>',
							'</div>',
						'</div>'
					);
			
			tpl.append(this.body, reasonArray['aussie-based']);
			
		} else {

			var el	= null;
			
			if (reasonArray['international-assignment']['reasons'].length > 0) {
				var tpl = new Ext.XTemplate(
					'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
						'<div class="tmn-authorisation">',
							'<div class="header">International Assignment - Reasons for needing Authorisation</div>',
							'<tpl for="reasons"><div class="indent">- {reason}</div></tpl>',
						'</div>',
					'</div>'
				);
		
				el = tpl.append(this.body, reasonArray['international-assignment'], true);
			}
			
			if (reasonArray['international-assignment']['reasons'].length > 0) {
				
				var home_tpl;
				
				//if no international reasons were added add the container to the body as well other wise
				//add a break and append the home reasons
				if (el == null) {
					el = this.body;
					
					home_tpl = new Ext.XTemplate(
							'<div id="tmn-' + this.id + '-authorisation-div" class="tmn-page">',
								'<div class="tmn-authorisation">',
									'<div class="header">Home Assignment - Reasons for needing Authorisation</div>',
									'<tpl for="reasons"><div class="indent">- {reason}</div></tpl>',
								'</div>',
							'</div>'
						);
				} else {
					home_tpl = new Ext.XTemplate(
							'<p>&nbsp;</p>',
							'<div class="tmn-authorisation">',
								'<div class="header">Home Assignment - Reasons for needing Authorisation</div>',
								'<tpl for="reasons"><div class="indent">- {reason}</div></tpl>',
							'</div>'
						);
				}
		
				home_tpl.append(el, reasonArray['home-assignment']);
			}
			
		}
	},
	
	hidePanel: function() {
		this.hide();
	}
});
