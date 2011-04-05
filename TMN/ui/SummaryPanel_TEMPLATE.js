
Ext.ns('tmn', 'tmn.view');

/**
 * @class		SummaryPanel
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
tmn.view.SummaryPanel = function(view, config) {
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
	var id					=	config.id || 'summary_panel';
	
	/**
	 * holds the response for each form after it is submitted successfully (accessed by 'international-assignment', 'home-assignment' or 'aussie-based'
	 * (each of these is also has 'single' or 'spouse') ie templates['aussie-based']['single'])
	 * @type An Associative Array of Associative arrays of Strings
	 */
	this.templates = {};
	this.templates['aussie-based'] = {};
	this.templates['international-assignment'] = {};
	this.templates['home-assignment'] = {};

	//defines the aussie based template for displaying the TMN
	//ABS

	//ABS
	
	//ABC

	//ABC

	//defines the international assignmnet section of the template for displaying the TMN
	//IAS

	//IAS

	//IAC

	//IAC

	//defines the home assignmnet section of the template for displaying the TMN
	//HAS

	//HAS
	
	//HAC

	//HAC

	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	var config =  {
			itemId:		id,
			frame:		true,
			header:		false,
			bodyStyle:	'padding:0'
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.FormPanel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.SummaryPanel.superclass.constructor.call(this, config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.SummaryPanel, Ext.Panel, {
	
	/**
	 * Loads the Panel with the users TMN data
	 * @param {Object}	response	An Object/Associative Array of strings that contains all the reponses from form submittions.
	 * 								This holds the data that is to be displayed using templates on this form.
	 */
	renderSummary: function(data, isOverseas, hasSpouse) {
		
		//make sure there are values to display (if the values are missing then show an error message)
		if (data['aussie-based'] !== undefined || (data['international-assignment'] !== undefined && data['home-assignment'] !== undefined)) {
			//display the appropriate template based on the user's details
			if (isOverseas) {
				if (hasSpouse){
					this.templates['international-assignment']['spouse'].overwrite(this.body, data['international-assignment']);
					this.templates['home-assignment']['spouse'].append(this.body, data['home-assignment']);
				} else {
					this.templates['international-assignment']['single'].overwrite(this.body, data['international-assignment']);
					this.templates['home-assignment']['single'].append(this.body, data['home-assignment']);
				}
			} else {
				if (hasSpouse){
					this.templates['aussie-based']['spouse'].overwrite(this.body, data['aussie-based']);
				} else {
					this.templates['aussie-based']['single'].overwrite(this.body, data['aussie-based']);
				}
			}
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
	 * Prints the users TMN then send the data to the backend to be stored in the Database
	 */
	printSummary: function() {
		//print it
		if (Ext.isChrome) {
			window.print();
		} else {
			Ext.ux.Printer.print(this);
		}
	}
});
