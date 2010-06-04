
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
	
	//ABC

	//defines the international assignmnet section of the template for displaying the TMN
	//IAS

	//IAC

	//defines the home assignmnet section of the template for displaying the TMN
	//HAS
	
	//HAC

	//defines the international assignmnet authorisation section of the template for displaying the TMN
	//IAAS
	
	//IAAC

	//defines the home assignmnet authorisation section of the template for displaying the TMN
	//HAAS
	
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
