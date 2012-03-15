
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
	this.submit_url			=	config.submit_url || 'php/submit_tmn_for_authorisation.php';
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
			'submitfailure',
			
			/**
			 * @event tmnsubmitted
			 * Fires after the user has hit the submit button and the tmn has successfully been sent for submittion
			 */
			'tmnsubmitted'
	);
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
			id:			this.id,
			frame:		true,
			title:		this.title,
			bodyCfg: {tag:'center'},
			bodyStyle:	'padding:0',
			items:		[
			    {
					xtype:	'button',
					itemId:	'submit_button',
					text:	'SUBMIT',
					width:	200,
					scale:	'large',
					style:	'margin:5px 0px 15px 0px',
					scope:	this,
					handler:function(button, event) {
						
						var dataObj,
							authorisersObj,
							level_1_panel	= this.items.map['authorisation_level_1_panel'],
							level_2_panel	= this.items.map['authorisation_level_2_panel'],
							level_3_panel	= this.items.map['authorisation_level_3_panel'];
						
						//make sure all the authorisers have been selected
						if (level_1_panel.isValid() && level_2_panel.isValid() && level_3_panel.isValid()) {
							
							//prepare the data for sending
							dataObj			= this.prepareData(this.data);
							authorisersObj	= this.prepareAuthorisers(level_1_panel.getData(), level_2_panel.getData(), level_3_panel.getData());
							
							Ext.Ajax.request({											//send all the data about the misso to the server for processing
								url: this.submit_url,
								params: {
									session:		this.getSession(),
									authorisers:	Ext.util.JSON.encode(authorisersObj),
									data:			Ext.util.JSON.encode(dataObj)
								},
								success: function(response, options){
									
									var responseObj	= Ext.decode(response.responseText);
									if (responseObj.success == true) {
										
										this.fireEvent('tmnsubmitted');
										this.getComponent('submit_button').disable();
										
										Ext.MessageBox.show({
											icon: Ext.MessageBox.INFO,
											buttons: Ext.MessageBox.OK,
											closable: false,
											title: 'Thank You',
											msg: 'Your TMN has been successfully submitted for approval. You will recieve updates on its approval at the following email address: ' + responseObj.email + '.'
										});
										
									} else {
										
										var responseObj	= Ext.decode(response.responseText),
										locked		= false,
										errorMsg;
									
										if (responseObj.alert !== undefined) {
											errorMsg = responseObj.alert;
										} else {
											errorMsg = 'Your submission process didn\'t work, please try again.';
										}
										
										if (responseObj.locked !== undefined) {
											locked		= responseObj.locked;
										}
										
										Ext.MessageBox.show({
											icon: Ext.MessageBox.ERROR,
											buttons: Ext.MessageBox.OK,
											closable: false,
											title: 'Error',
											msg: errorMsg
										});
										
										if (locked) {
											this.getComponent('submit_button').disable();
										}
									}
								},
								failure: function(response, options) {
									
									var responseObj	= Ext.decode(response.responseText),
										locked		= false,
										errorMsg;
									
									if (responseObj.alert !== undefined) {
										errorMsg = responseObj.alert;
									} else {
										errorMsg = 'Your submission process didn\'t work, please try again.';
									}
									
									if (responseObj.locked !== undefined) {
										locked		= responseObj.locked;
									}
									
									Ext.MessageBox.show({
										icon: Ext.MessageBox.ERROR,
										buttons: Ext.MessageBox.OK,
										closable: false,
										title: 'Error',
										msg: errorMsg
									});
									
									if (locked) {
										this.getComponent('submit_button').disable();
									}
								},
								scope: this
							});
						} else {
							Ext.MessageBox.show({
								icon: Ext.MessageBox.ERROR,
								buttons: Ext.MessageBox.OK,
								closable: false,
								title: 'Error',
								msg: 'You haven\'t selected all your authorisers. Please do that before you submit.'
							});
						}
					}
				},
				new tmn.view.AuthorisationPanel(this, {
					id: 	'authorisation_level_3_panel',
					title:	'Authorisation Level Three',
					leader:	'Level 3',
					mode:	'level_3'
				}),
				new tmn.view.AuthorisationPanel(this, {
					id: 	'authorisation_level_2_panel',
					title:	'Authorisation Level Two',
					leader:	'Level 2',
					mode:	'level_2'
				}),
				new tmn.view.AuthorisationPanel(this, {
					id: 	'authorisation_level_1_panel',
					title:	'Authorisation Level One',
					leader:	'Level 1',
					mode:	'level_1'
				}),
				new tmn.view.SummaryPanel(this, {
					id:		'summary_panel'
				})
			]
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
	 * Takes the response object and decodes all the json strings into javascript objects. The usefull javascript objects are put into an array,
	 * which is returned.
	 * The objects will be stored in this.values['aussie-based'], this.values['home-assignment'] or this.values['international-assignment']
	 * based on which set of data has been passed to it.
	 * @param {Object}	response	An Object/Associative Array of json strings that contains all the reponses from form submittions.
	 * 								This holds the data that is to be displayed using templates on this form.
	 */
	parseResponse: function(response) {
		var returnObj	= {},
			returnArray	= null;
		
		//decode the response strings into json objects
		for (id in response) {
			form = Ext.getCmp(id);
			if (form.aussie_form == true && form.overseas_form == false) {
				if (returnArray	== null) {returnArray = {};}
				returnObj = Ext.util.JSON.decode(response[id]);
				returnArray['aussie-based'] = returnObj['tmn_data'];
			} else if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == true) {
				if (returnArray	== null) {returnArray = {};}
				returnObj = Ext.util.JSON.decode(response[id]);
				returnArray['home-assignment'] = returnObj['tmn_data'];
			} else if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == false) {
				if (returnArray	== null) {returnArray = {};}
				returnObj = Ext.util.JSON.decode(response[id]);
				returnArray['international-assignment'] = returnObj['tmn_data'];
			}
		}
		
		return returnArray;
	},
	
	gatherLevelReasons: function(data, level) {
		
		var levelReasons	= {};
			totalReasons	= 0;
		
		if (data['aussie-based'] !== undefined) {
			if (data['aussie-based']['auth_lv' + level + '_reasons']) {
				levelReasons['aussie-based']						= {};
				levelReasons['aussie-based']['reasons']				= data['aussie-based']['auth_lv' + level + '_reasons'];
				totalReasons										+= levelReasons['aussie-based']['reasons'].length;
			}
		//TODO: change to if international assignment
		} else {
			if (data['home-assignment']['auth_lv' + level + '_reasons'] !== undefined) {
				levelReasons['home-assignment']						= {};
				levelReasons['home-assignment']['reasons']			= data['home-assignment']['auth_lv' + level + '_reasons'];
				totalReasons										+= levelReasons['home-assignment']['reasons'].length;
			}
			
			if (data['international-assignment']['auth_lv' + level + '_reasons'] !== undefined) {
				levelReasons['international-assignment']			= {};
				levelReasons['international-assignment']['reasons']	= data['international-assignment']['auth_lv' + level + '_reasons'];
				totalReasons										+= levelReasons['international-assignment']['reasons'].length;
			}
			
		}

		levelReasons['total']	= totalReasons;
		
		return levelReasons;
	},
	
	prepareData: function(data) {
		
		var dontCopyTheseFieldsArray	= {transfers:null, auth_lv1:null, auth_lv1_reasons: null, auth_lv2:null, auth_lv2_reasons: null, auth_lv3:null, auth_lv3_reasons: null},
			returnObj					= {};
		
		if (data['aussie-based'] !== undefined) {
			returnObj['aussie-based']		= {};
			//delete unneeded aussie data
			for (field in data['aussie-based']) {
				if (dontCopyTheseFieldsArray[field] === undefined) {
					returnObj['aussie-based'][field] = data['aussie-based'][field];
				}
			}
		} else {
			
			returnObj['international-assignment']		= {};
			//delete unneeded aussie data
			for (field in data['international-assignment']) {
				if (dontCopyTheseFieldsArray[field] === undefined) {
					returnObj['international-assignment'][field] = data['international-assignment'][field];
				}
			}
			
			returnObj['home-assignment']		= {};
			//delete unneeded aussie data
			for (field in data['home-assignment']) {
				if (dontCopyTheseFieldsArray[field] === undefined) {
					returnObj['home-assignment'][field] = data['home-assignment'][field];
				}
			}
		}
		
		return returnObj;
	},
	
	prepareAuthorisers: function(level_1_data, level_2_data, level_3_data) {
		var returnObj	= {};
		
		returnObj['level_1']	= level_1_data;
		returnObj['level_2']	= level_2_data;
		returnObj['level_3']	= level_3_data;
		
		return returnObj;
	},
	
	/**
	 * Loads the Panel with the users TMN data
	 * @param {Object}	response	An Object/Associative Array of strings that contains all the reponses from form submittions.
	 * 								This holds the data that is to be displayed using templates on this form.
	 */
	loadForm: function(response) {
		
		this.getComponent('submit_button').enable();
		
		if (response !== undefined) {
			//return the object decribed by the json strings in response
			var values			= this.parseResponse(response),
				level2Values	= this.gatherLevelReasons(values, 2),
				level3Values	= this.gatherLevelReasons(values, 3),
				summary_panel	= this.items.map['summary_panel'],
				level_2_panel	= this.items.map['authorisation_level_2_panel'],
				level_3_panel	= this.items.map['authorisation_level_3_panel'];
			
			//put the data away for submit
			this.data		= values;
			
			if (level3Values.total > 0) {
				
				//display the values that were just parsed
				if (level_3_panel.body !== undefined) {
					level_3_panel.showPanel(level3Values);
				} else {
					level_3_panel.on('afterrender', level_3_panel.showPanel.createDelegate(level_3_panel, [level3Values]));
				}
				
			} else {
				//display the values that were just parsed
				if (level_3_panel.body !== undefined) {
					level_3_panel.hidePanel();
				} else {
					level_3_panel.on('afterrender', level_3_panel.hidePanel);
				}
			}
			
			if (level3Values.total > 0 || level2Values.total > 0) {
				
				//display the values that were just parsed
				if (level_2_panel.body !== undefined) {
					level_2_panel.showPanel(level2Values);
				} else {
					level_2_panel.on('afterrender', level_2_panel.showPanel.createDelegate(level_2_panel, [level2Values]));
				}
				
			} else {
				//display the values that were just parsed
				if (level_2_panel.body !== undefined) {
					level_2_panel.hidePanel();
				} else {
					level_2_panel.on('afterrender', level_2_panel.hidePanel);
				}
			}
			
			if (values != null) {
				
				//display the values that were just parsed
				if (summary_panel.body !== undefined) {
					summary_panel.renderSummary(values, this.isOverseas(), this.hasSpouse());
				} else {
					summary_panel.on('afterrender', summary_panel.renderSummary.createDelegate(summary_panel, [values, this.isOverseas(), this.hasSpouse()]));
				}
				
			} else {
				this.showDataErrorMsg();
			}
		} else {
			this.showDataErrorMsg();
		}
	},
	
	showDataErrorMsg: function() {
		Ext.MessageBox.show({
			icon: Ext.MessageBox.ERROR,
			buttons: Ext.MessageBox.OK,
			closable: false,
			title: 'Error',
			msg: 'This form was loaded with invalid data. Please refresh your page and try again. If the problem doesn\'t go away, Please Contact The Technology Team at <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>'
		});
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
		this.items.map['summary_panel'].printSummary();
	},
	
	/**
	 * Thanks the user for submitting their TMN.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitSuccess: function (form, action) {
		
	},
	
	/**
	 * This handler deals with the failure of the submit request for this panel.
	 * @param {Ext.form.BasicForm}	form		The Object that represents the form that failed (see {@link Ext.form.BasicForm})
	 * @param {Ext.form.Action}		action		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
	 */
	onSubmitFailure: function (form, action){

	}
});
