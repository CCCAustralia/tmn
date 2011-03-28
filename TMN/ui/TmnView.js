
Ext.ns('tmn', 'tmn.view');		//the namespace of the project

/**
 * @class		tmn.view.TmnView
 * 
 * <p>
 * <b>Description:</b> The view for the tmn.<br />
 * Manages the forms that make up the TMN. It holds all the forms, switches between them, passes info & commands
 * down from the controller to the forms and passes (bubbles) events up from forms to the controller.
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
tmn.view.TmnView = function() {
	
	//class wide variables definitions
	/**
	 * This is the array the forms are stored in, when they become active they are selected by their index
	 * and added to this.items (they remain in this array at all times, this.items has a reference to an element in this array).<br />
	 * If your need to add a form, create an instance your new class in this array.
	 * @type Array
	 * @note The order of this array is the order the forms will be displayed in.
	 */
	this.forms = [																//this is the array the forms are stored in, when they become active they are selected by their index
	              	new tmn.view.PersonalDetailsForm(this),						// and added to this.items
	              	new tmn.view.FinancialDetailsForm(this, {id:'international_assignment_form', title:'International Assignment', home_assignment:false, aussie_form:false, overseas_form:true}),	//(they remain in this array at all times, this.items has a reference to an element in this array)
	              	new tmn.view.FinancialDetailsForm(this, {id:'home_assignment_form', title:'Home Assignment', home_assignment:true, aussie_form:false, overseas_form:true}),
	              	new tmn.view.FinancialDetailsForm(this),
	              	new tmn.view.PrintForm(this)
	             ];
	/**
	 * This is an index of the forms array. It tells you which form is currently being interacted with by the user.
	 * @type Number
	 * @note Do Not use. If you want access to the active from or you want to perform an action on the active for use the methods provided by this class.
	 */
	this.active			=	0;													//set which form is active (viewable)
	
	/**
	 * The users marital status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users marital status use the methods provided by this class.
	 */
	this.spouse			=	false;												//set if a spouse is present or not
	
	/**
	 * The users overseas status. All javascript classes in the tmn keep track of this.
	 * @type Boolean
	 * @note Do Not use. If you want access to the users overseas status use the methods provided by this class.
	 */
	this.overseas		=	false;												//set if a missionary is serving overseas

	//register events
	this.addEvents(
		//personal details events
		'single',
		'married',
		'overseas',
		'aussie',
		'mpdyes',
		'mpdno',
		'fulltime',
		'parttime',
		'loadsuccess',
		'loadfailure',
		'submitsuccess',
		'submitfailure',
		
		//financial details events
		'saveresponse',
		'financialdataupdated',

		//nav events
		/**
         * @event next
         * Fires when the user clicks the next button.
         */
		'next',
		/**
         * @event previous
         * Fires when the user clicks the previous button.
         */
		'previous'
	);
	
	// relay general events
	for (formCount = 0; formCount < this.forms.length; formCount++) {			//for each form
		this.relayEvents(this.forms[formCount], [
		                                         	'financialdataupdated',
													'loadsuccess',
													'loadfailure',
													'submitsuccess',
													'submitfailure'
		                                         ]);
	}
	// relay special Personal Details events
	this.relayEvents(this.forms[0], [
										'single',
										'married',
										'overseas',
										'aussie',
										'mpdyes',
										'mpdno',
										'fulltime',
										'parttime'
                                     ]);
	
	// relay special Financial Details events
	for (formCount = 1; formCount < 4; formCount++) {
		this.relayEvents(this.forms[formCount], [
		                                    'resetfinancialdata',
											'loadsession',
											'loadsessionsuccess',
											'savesession',
											'saveassession',
											'deletesession'
	                                     ]);
	}
	
	// relay special Print Form events
	this.relayEvents(this.forms[4], [
										'next'
                                     ]);
	
	/**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	this.config = {
			id: 'tmn_container',
			frame: true,header: false,
			renderTo: 'tmn-cont',
			width:900,
			items: [this.forms[0]],
			bbar: [
					{
						itemId: 'prev_button',
						text: 'Previous',
						scope: this,
						handler: function(){
							this.fireEvent('previous');
						},
						disabled: true
					},'->',{
						itemId: 'next_button',
						text: 'Next',
						scope: this,
						handler: function(){
							this.fireEvent('next');
						}
					}
			]
	};
	
	//this is a call to tmn.view.TmnView's parent constructor (Ext.Panel), this will give tmn.view.TmnView all the variables and methods that it's parent does
	tmn.view.TmnView.superclass.constructor.call(this, this.config);
};

//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.TmnView, Ext.Panel, {
	
	/**
	 * Changes which form is viewed. It hides the currently active one and shows the form at position index.
	 * @param {integer} index			The index of the form you want to swap in.
	 */
	changeForm: function(index){

		//if valid index do change
		if (index >= 0 && index < this.forms.length) {
			//grab the currently active form (old_form) and the next form to be active (next_form)
			var active_form = this.forms[this.active];
			this.active = index;
			var next_form = this.forms[index];
			
			//hide the errors at the bottom of the page before the change
			if (active_form.bottomToolbar !== undefined) {
				if (active_form.bottomToolbar.plugins !== undefined) {
					if (active_form.bottomToolbar.plugins.getMsgEl().isVisible()) {
						active_form.bottomToolbar.plugins.hideErrors();
					}
				}
	        }
	
			//if the form is rendered then fade out the active form and fade in the next form.
			if (active_form.el !== undefined) {
				active_form.el.fadeOut({callback: function (active_form, next_form) {	//fade out the active form
					this.add(next_form);												//add the next form into the items array/collection of this object
					active_form.hide();													//hide the active form so it doesn't reappear after the fade out animation (hides via css visiblity properties)
					next_form.show();													//shows the next form (ie changes its css properties so it can be seen)
					this.remove(active_form, false);									//remove the active form from this objects items array/collection (the false mean it will not be destroyed but will remain a member of the forms array)
					this.doLayout();													//renders these changes to the browser
					next_form.el.fadeIn();												//fades in the next form so when it is rendered it looks good
				}.createDelegate(this, [active_form, next_form])});
			}
		}
	},
	
	/**
	 * Returns a reference to the currently active form.
	 * @returns {Ext.form.FormPanel} 	A reference to the form that is currently being displayed.
	 */
	getActiveForm: function() {
		return this.forms[this.active];
	},
	
	/**
	 * Returns a reference to the form at the passed index.
	 * @returns {Ext.form.FormPanel} 	A reference to the form that is at index.
	 */
	getFormAt: function(index) {
		return this.forms[index];
	},
	
	/**
	 * Loads the currently active form using its loadForm method.
	 */
	loadActiveForm: function(local_data) {
		if (local_data === undefined) {
			this.forms[this.active].loadForm();
		} else {
			this.forms[this.active].loadForm(local_data);
		}
	},
	
	/**
	 * Submits the currently active form using its submitForm method.
	 */
	submitActiveForm: function() {
		this.forms[this.active].submitForm();
	},
	
	/**
	 * Enables the previous button in the bottom toolbar.
	 */
	enablePrevious: function() {
		this.getBottomToolbar().items.map['prev_button'].enable();
	},
	
	/**
	 * Disables the previous button in the bottom toolbar.
	 */
	disablePrevious: function() {
		this.getBottomToolbar().items.map['prev_button'].disable();
	},
	
	/**
	 * Sets the text of the next button in the bottom toolbar.
	 */
	changePreviousText: function(text) {
		this.getBottomToolbar().items.map['prev_button'].setText(text);
	},
	
	/**
	 * Enables the next button in the bottom toolbar.
	 */
	enableNext: function() {
		this.getBottomToolbar().items.map['next_button'].enable();
	},
	
	/**
	 * Disables the next button in the bottom toolbar.
	 */
	disableNext: function() {
		this.getBottomToolbar().items.map['next_button'].disable();
	},
	
	/**
	 * Sets the text of the next button in the bottom toolbar.
	 */
	changeNextText: function(text) {
		this.getBottomToolbar().items.map['next_button'].setText(text);
	},
	
	/**
	 * Returns the users marital status.
	 */
	hasSpouse: function() {return this.spouse;},

	/**
	 * Returns the users overseas status.
	 */
	isOverseas: function() {return this.overseas;},
	
	/**
	 * Propagates the user's marrital status throughout all the forms.
	 * @param {boolean} spouse			The user's marrital status (true=spouse, false=single).
	 */
	setSpouse: function(spouse) {
		this.spouse = spouse;
		
		for (formCount = 0; formCount < this.forms.length; formCount++)			//for each form
			this.forms[formCount].setSpouse(spouse);							//set the form's marrital status
	},
	
	/**
	 * Propagates the user's overseas status throughout all the forms.
	 * @param {boolean} overseas		The user's overseas status (true=serves overseas, false=serves locally).
	 */
	setOverseas: function(overseas) {
		this.overseas = overseas;
		
		for (formCount = 0; formCount < this.forms.length; formCount++)			//for each form
			this.forms[formCount].setOverseas(overseas);						//set the form's overseas status
	},
	
	/**
	 * Returns the index of the first aussie form.
	 * @returns {integer}				The index of the first aussie form.
	 */
	indexOfFirstAussieForm: function() {
		for(formCount = 0; formCount < this.forms.length; formCount++) {
			if (this.forms[formCount].aussie_form == true) {
				break;
			}
		}
		return formCount;
	},
	
	/**
	 * Returns the index of the first overseas form.
	 * @returns {integer}				The index of the first overseas form.
	 */
	indexOfFirstOverseasForm: function() {
		for(formCount = 0; formCount < this.forms.length; formCount++) {
			if (this.forms[formCount].overseas_form == true) {
				break;
			}
		}
		return formCount;
	},
	
	/**
	 * Returns the index of the last aussie form.
	 * @returns {integer}				The index of the last aussie form.
	 */
	indexOfLastAussieForm: function() {
		for(formCount = this.forms.length - 1; formCount >= 0; formCount--) {
			if (this.forms[formCount].aussie_form == true) {
				break;
			}
		}
		return formCount;
	},
	
	/**
	 * Returns the index of the last overseas form.
	 * @returns {integer}				The index of the last overseas form.
	 */
	indexOfLastOverseasForm: function() {
		for(formCount = this.forms.length - 1; formCount >= 0; formCount--) {
			if (this.forms[formCount].overseas_form == true) {
				break;
			}
		}
		return formCount;
	},
	
	/**
	 * Returns the number of forms in this class.
	 * @returns {integer}				The number of forms in this class.
	 */
	length: function() {
		return this.forms.length;
	},
	
	/**
	 * Returns the number of forms in this class.
	 * @returns {integer}				The number of forms in this class.
	 */
	aussieLength: function() {
		var aussieLength = 0;
		for(formCount = 0; formCount < this.forms.length; formCount++) {
			if (this.forms[formCount].aussie_form == true) {
				aussieLength++;
			}
		}
		return aussieLength;
	},
	
	/**
	 * Returns the number of forms in this class.
	 * @returns {integer}				The number of forms in this class.
	 */
	overseasLength: function() {
		var overseasLength = 0;
		for(formCount = 0; formCount < this.forms.length; formCount++) {
			if (this.forms[formCount].overseas_form == true) {
				overseasLength++;
			}
		}
		return overseasLength;
	}
	
}); //eo extend
