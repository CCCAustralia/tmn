
Ext.BLANK_IMAGE_URL = 'lib/resources/images/default/s.gif';	//the path of Ext's blank image (used for
Ext.ns('tmn');												//the namespace of the project

/**
 * @class 		tmn.TmnController
 *
 * <p>
 * <b>Description:</b> Entry point for the tmn.<br />
 * Manages the User Interface (the view) and interactions between the User Interface and the PHP backend (the model).
 * TmnController contains handlers that are executed when events are fired from this.view or objects created by this.view.
 * The handlers in this class manage any changes that need to be made across the TMN.
 * </p>
 * <br />
 * <p>
 * <b>If you want to change any ranges, bands, tax rates, etc in the TMN, these are the steps:</b><br />
 * - DO NOT EDIT ANY CODE!!!<br />
 * - Refer to the Member Care Documentation about changing TMN values.
 * </p>
 * <p>
 * <b>If you want to make structural changes to the User Interface of the TMN, these are the steps:</b><br />
 * If you want to edit a field in a form, I suggest:<br />
 * - Look at the config variable in the constructor of the Classes you want to change.<br />
 * - Find the part you want to change and find the xtype of it.<br />
 * - Search for that type in the API. When you have found the config option you want, return and change it.<br />
 * If you want to make more major changes:<br />
 * - Change the config variable in the constructor of the class you want to change.
 * </p>
 * <p>
 * <b>If you want to add functionality to the TMN, these are the steps:</b><br />
 * - Write a handler here in TmnController, this should run a list of commands that manage any changes that need to be made across the TMN (eg changeForm(); loadActiveForm();)<br />
 * - Write a function in {@link tmn.view.TmnView} (or a class that is underneath TmnView, eg PersonalDetailsForm) that does the actual work of changing the form or loading the active form<br />
 * - Add an event to {@link tmn.view.TmnView} (and the object underneath the view if it is going to be used there). This happens in their constructor using {@link tmn.view.TmnView#addEvents}.<br />
 * - Attach your handler to the event by using {@link tmn.view.TmnView#on} in the {@link #init} method.<br />
 * - You can then run the code you just created by using this.fireEvent('eventname', arg1, arg2, ...) in the view or any object you added the event to.
 * </p>
 * <p>
 * <b>If you want to change the way things are calculated in the TMN, these are the steps:</b><br />
 * - Refer to the TMN PHP API.
 * </p>
 * <p>
 * <b>If you want to change the Database in the TMN, these are the steps:</b><br />
 * - Refer to the TMN PHP API.
 * </p><br />
 *
 * @author		Michael Harrison	(<a href="mailto:michael.harrison@ccca.org.au">michael.harrison@ccca.org.au</a>)
 * 				& Thomas Flynn		(<a href="mailto:tom.flynn@ccca.org.au">tom.flynn@ccca.org.au</a>)
 *
 * @namespace 	tmn
 * @version		TMN 2.1.0
 * @note		The TMN uses the MVC design structure, read up on it at <a href="http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller">http://en.wikipedia.org/wiki/Model-view-controller</a>).
 * @demo		http://mportal.ccca.org.au/TMN
 */
tmn.TmnController = function() {

	return {

		/**
		 * holds the tmn's view ({@link tmn.view.TmnView}). Set in init, Do Not edit!
		 * @type TmnView
		 */
		view: null,		//holds the tmn's view (tmn.view.TmnView)

		/**
		 * holds the financial data for each form as it gets processed (indexed by form's id)
		 * @type Associative array of Objects
		 */
		financial_data: {},

		/**
		 * Returns the form named by form_name.
		 * @param	{String}		form_name	The name of the form we are searching for (can have the values: 'international-assignment', 'home-assignment' or 'aussie-based').
		 * 										If its not present the associative array/object of financial data will be returned.
		 * @returns	{Ext.form.FormPanel} 		The Object that represents the form (see {@link Ext.form.FormPanel})<br />
		 */
		getForm: function(form_name) {
			var form = null;

			switch (form_name) {
				case 'international-assignment':

					for (var formCount = 0; formCount < this.view.length(); formCount++) {
						form = this.view.getFormAt(formCount);
						if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == false)
							break;
					}
					break;
				case 'home-assignment':
					for (var formCount=0; formCount < this.view.length(); formCount++) {
						form = this.view.getFormAt(formCount);
						if (form.aussie_form == false && form.overseas_form == true && form.home_assignment == true)
							break;
					}
					break;
				case 'aussie-based':
					for (var formCount=0; formCount < this.view.length(); formCount++) {
						form = this.view.getFormAt(formCount);
						if (form.aussie_form == true && form.overseas_form == false)
							break;
					}
					break;
				default:
					break;
			}

			return form;
		},

		/**
		 * Returns the financial data of the form named by form_name.
		 * @param	{String}		form_name	The name of the form we are searching for (can have the values: 'international-assignment', 'home-assignment' or 'aussie-based').
		 * 										If its not present the associative array/object of financial data will be returned.
		 * @returns	{String/Object}				The set of data processed by the form. (if form not found or no form sent; it will return the associative array/object of financial data)
		 */
		getFinancialData: function(form_name) {
			//get the form object with that name
			var form = this.getForm(form_name);

			//if there was a form by that name returns its data
			if (form != null) {
				return this.financial_data[form.id];
			//if not return all data
			} else {
				return this.financial_data;
			}
		},

		/**
		 * holds the response for each form after it is submitted successfully (indexed by form's id)
		 * @type Associative array of Strings
		 */
		response: {},

		/**
		 * Returns the response of the form named by form_name.
		 * @param	{String}		form_name	The name of the form we are searching for (can have the values: 'international-assignment', 'home-assignment' or 'aussie-based').
		 * 										If its not present the associative array/object of responses will be returned.
		 * @returns	{String/Object}				The string that was returned by the form after a successful submition. (if form not found or no form sent;
		 * 										it will return the associative array/object of responses)
		 */
		getResponse: function(form_name) {
			//get the form object with that name
			var form = this.getForm(form_name);

			//if there was a form by that name returns its data
			if (form != null) {
				return this.response[form.id];
			//if not return all data
			} else {
				return this.response;
			}
		},

		/**
		 * Handler for when a user clicks next in the view; it will submit the form.
		 * (submit can have success or failure, look at those handlers to see what happens after submit)
		 */
		onNext: function() {
			var form = this.view.getActiveForm();

			//the following will make the user save before they continue

			//if its not a financial details form (ie its for both aussies and internationals) then just submit
			if (form.aussie_form && form.overseas_form) {
				this.view.submitActiveForm();
			//if its a financial details form
			} else {
				//if the form is saved then submit the form
				if (form.isSaved()) {
					this.view.submitActiveForm();
				//if it isn't saved ask the user to save
				} else {
					Ext.MessageBox.show({
						icon: Ext.MessageBox.WARNING,
						buttons: Ext.MessageBox.OK,
						closable: false,
						title: 'Warning!',
						msg: 'You have unsaved changes on this page, you must save before you can continue.'
					});
				}
			}
		},

		/**
		 * Handler for when a user clicks previous in the view.
		 * it will hide the active form and show the previous form.
		 */
		onPrevious: function() {
			index = this.view.active;		//grabs the index of the previous form
			if (this.overseas == true) {
				do {index--;} while (this.view.getFormAt(index).overseas_form == false);

				if (this.view.length == 1){
					this.view.disablePrevious();
					this.view.disableNext();
				} else {
					if (index == this.view.indexOfFirstOverseasForm()) {					//does bound checking on the index
						this.view.disablePrevious();	//if it is the minimum bound, disable the previous button
						this.view.enableNext();
					} else {
						this.view.enablePrevious();		//if it is a regular index enable buttons
						this.view.enableNext();
					}
				}
			} else {
				do {index--;} while	(this.view.getFormAt(index).aussie_form == false);

				if (this.view.length == 1){
					this.view.disablePrevious();
					this.view.disableNext();
				} else {
					if (index == this.view.indexOfFirstAussieForm()) {					//does bound checking on the index
						this.view.disablePrevious();	//if it is the minimum bound, disable the previous button
						this.view.enableNext();
					} else {
						this.view.enablePrevious();		//if it is a regular index enable buttons
						this.view.enableNext();
					}
				}
			}
			this.view.changeNextText('Next');	//make sure the buttons say the right thing
			Ext.History.add(index);				//update history
			this.view.changeForm(index);		//hides the current form and shows the form with the index we just passed it

			//resize the view to better fit the form that was just switched to
			this.doResize();
		},

		/**
		 * Handler for when a form's load ajax request is a success.
		 * Will call the form's handler as the actions are specific to the form.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Ext.form.BasicForm} 	form: 			The Object that represents just the form (see {@link Ext.form.BasicForm})<br />
		 * @param {Ext.form.Action} 	action: 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
		 */
		onLoadSuccess: function(form_panel, form, action) {

			form_panel.onLoadSuccess(form, action);
		},

		/**
		 * Handler for when a form's load ajax request is a failure.
		 * Will call the form's handler to do form specific error handling.
		 *
		 * @param {Ext.form.FormPanel}	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Ext.form.BasicForm}	form: 			The Object that represents just the form (see {@link Ext.form.BasicForm})<br />
		 * @param {Ext.form.Action} 	action: 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
		 */
		onLoadFailure: function(form_panel, form, action) {

			form_panel.onLoadFailure(form, action);
		},

		/**
		 * Handler for when a form's submit ajax request is a success.
		 * Will call the form's handler as the actions are specific to the form.
		 * It then changes in the next form and loads it.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Ext.form.BasicForm} 	form: 			The Object that represents just the form (see {@link Ext.form.BasicForm})<br />
		 * @param {Ext.form.Action} 	action: 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
		 */
		onSubmitSuccess: function(form_panel, form, action) {

			var index = this.view.active;			//grabs the index of the current form
			if (this.overseas == true) {
				do {index++;} while	(this.view.getFormAt(index).overseas_form == false);
				if (this.view.length == 1){
					this.view.disablePrevious();
					this.view.disableNext();
					this.view.changeNextText('Next');
					this.view.removeNextQuicktip();		//make sure the buttons doesn't have any quicktips attached to it
				} else {
					if (index == this.view.indexOfLastOverseasForm()) {		//does bound checking on the index
						this.view.enablePrevious();
						this.view.enableNext();
						this.view.changeNextText('Print');		//if it is the maximum bound, Change text of Next Button			//add a quick tip to the next button telling people about the pdf creator
					} else {
						this.view.enablePrevious();				//if it is a regular index enable buttons
						this.view.enableNext();
						this.view.changeNextText('Next');
					}
				}
			} else {
				do {index++;} while	(this.view.getFormAt(index).aussie_form == false);
				if (this.view.length == 1){
					this.view.disablePrevious();
					this.view.disableNext();
					this.view.changeNextText('Next');
				} else {
					if (index == this.view.indexOfLastAussieForm()) {		//does bound checking on the index
						this.view.enablePrevious();
						this.view.enableNext();
						this.view.changeNextText('Print');		//if it is the maximum bound, Change text of Next Button
					} else {
						this.view.enablePrevious();				//if it is a regular index enable buttons
						this.view.enableNext();
						this.view.changeNextText('Next');
					}
				}
			}

			form_panel.onSubmitSuccess(form, action);						//does any local changes needed after a successful submition
			this.response[form_panel.id] = action.response.responseText;	//save the response of the form

			if ((this.overseas == true && index <= this.view.indexOfLastOverseasForm()) || (this.overseas == false && index <= this.view.indexOfLastAussieForm())) {
				this.view.changeForm(index);//hides the current form and shows the form with the index we just passed it
				Ext.History.add(index);											//add this form to the browsers history

				//load the new form
				if (this.view.getActiveForm().rendered) {
					this.loadHandler(form_panel);														//loads new form
				} else {
					this.view.getActiveForm().on('afterrender', this.loadHandler, this, {single:true});	//sets new form to load once its rendered
				}
			}
		},

		/**
		 * Handler that will load the active form once its ready to be loaded.
		 * It will check what type of form it is to load and will do the appropriate actions needed to load that form.
		 * It is only called by {@link #onSubmitSuccess} because the only case where you need to load a form is when the last one was successfully submitted and the new one is put in its place.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the form that just successfully submitted.
		 * 												This object contains the form and the full panel that contains the form (see {@link Ext.form.FormPanel})
		 */
		loadHandler: function(form_panel) {
			if (this.view.getActiveForm().home_assignment == true) {
				console.log("ha", this.getFinancialData('international-assignment'));
				this.view.loadActiveForm(this.getFinancialData('international-assignment'));		//loads the new form with local data

				this.view.getActiveForm().disableStartDate();

				this.view.getActiveForm().onLoadSuccess(this.view.getActiveForm().getForm());
			} else if (this.view.active == this.view.indexOfLastAussieForm() || this.view.active == this.view.indexOfLastOverseasForm()) {
				console.log("la", this.response);
				//
				if (this.overseas == true) {
					this.view.getActiveForm().setSession(this.getFinancialData('international-assignment')['session_id']);
				} else {
					this.view.getActiveForm().setSession(this.getFinancialData('aussie-based')['session_id']);
				}
				this.view.loadActiveForm(this.response);
			} else {
				console.log("other");

				this.view.loadActiveForm();						//loads the new form
			}


			//resize the view to better fit the form that was just switched to
			this.doResize();
		},

		/**
		 * Handler for when a form's submit ajax request is a failure.
		 * Will call the form's handler as the actions are specific to the form.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Ext.form.BasicForm} 	form: 			The Object that represents just the form (see {@link Ext.form.BasicForm})<br />
		 * @param {Ext.form.Action} 	action: 		The action Object created from the ajax repsonse (see {@link Ext.form.Action})
		 */
		onSubmitFailure: function(form_panel, form, action) {

			form_panel.onSubmitFailure(form, action);
		},

		/**
		 * Handler for when the user indicates they are Full Time.
		 * If the user or the spouse indicates they are Full Time make their days per week invisable.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {boolean} 			spouse: 		A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
		 */
		onFullTime: function(form_panel, spouse) {

			if (spouse) {
				form_panel.setSpouseDaysPerWeekVisable(false);	//hide the part time field
				this.onOverseas(form_panel, true, false);		//set the overseas status to false because Full Time and Part Time are Aussie only states

				//if the user enters overseas for their spouse make sure they don't set their ftptos value to overseas
				if (form_panel.getFtptosValue() == 'Overseas') {
					form_panel.copySpouseFtptosValue();
					form_panel.fireEvent('fulltime', form_panel, false);
				}
			} else {
				form_panel.setDaysPerWeekVisable(false);		//hide the part time field
				this.onOverseas(form_panel, false, false);		//set the overseas status to false because Full Time and Part Time are Aussie only states

				//if the user has a spouse make sure the spouse doen't have their overseas status set to overseas (a couple can't serve in seperate countries)
				if (form_panel.hasSpouse() && form_panel.getSpouseFtptosValue() == 'Overseas') {
					form_panel.copyFtptosValue();
					form_panel.fireEvent('fulltime', form_panel, true);
				}
			}
		},

		/**
		 * Handler for when the user indicates they are Part Time.
		 * If the user or the spouse indicates they are Part Time make their days per week visable.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Boolean}				spouse: 		A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
		 */
		onPartTime: function(form_panel, spouse) {

			if (spouse) {
				form_panel.setSpouseDaysPerWeekVisable(true);	//show the part time field
				this.onOverseas(form_panel, true, false);		//set the overseas status to false because Full Time and Part Time are Aussie only states

				//if the user enters overseas for their spouse make sure they don't set their ftptos value to overseas
				if (form_panel.getFtptosValue() == 'Overseas') {
					form_panel.copySpouseFtptosValue();
					form_panel.fireEvent('parttime', form_panel, false);
				}
			} else {
				form_panel.setDaysPerWeekVisable(true);			//show the part time field
				this.onOverseas(form_panel, false, false);		//set the overseas status to false because Full Time and Part Time are Aussie only states

				//if the user has a spouse, make sure the spouse doen't have their overseas status set to overseas (a couple can't serve in seperate countries)
				if (form_panel.hasSpouse() && form_panel.getSpouseFtptosValue() == 'Overseas') {
					form_panel.copyFtptosValue();
					form_panel.fireEvent('parttime', form_panel, true);
				}
			}
		},

		/**
		 * Handler for when the user indicates if they serve overseas or not.
		 * When it runs, it uses {@link tmn.view.TmnView#setOverseas} to propagate the overseas status through to all the Objects that need to know.
		 * It also show/hides days per week, because if you serve overseas you are Full Time by default.
		 *
		 * @param {Ext.form.FormPanel}	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Boolean}				spouse: 		A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
		 * @param {Boolean} 			overseas		A boolean variable that tells the handler if the user serves overseas or not.
		 */
		onOverseas: function (form_panel, spouse, overseas) {

			//reset response when overseas status is changed
			this.resetResponse();

			if (overseas) {
				this.overseas = true;
				this.view.setOverseas(true);
				if (spouse) {											//an overseas misso is full time by defintion so days per week need to be hidden
					form_panel.setSpouseDaysPerWeekVisable(false);		//hide the days per week field
					form_panel.setDaysPerWeekVisable(false);		//hide the days per week field

					//if the user enters overseas for their spouse make sure the user's overseas is set to overseas too (a couple can't serve in seperate countries)
					form_panel.copySpouseFtptosValue();
				} else {
					form_panel.setDaysPerWeekVisable(false);			//hide the days per week field

					//if the user has a spouse, make sure the spouse has their overseas status set to overseas too (a couple can't serve in seperate countries)
					if (form_panel.hasSpouse()) {
						form_panel.copyFtptosValue();
						form_panel.setSpouseDaysPerWeekVisable(false);		//hide the days per week field
					}
				}
			} else {
				this.overseas = false;
				this.view.setOverseas(false);						//propergate overseas status through the view
			}
		},

		/**
		 * Handler for when the user changes their MPD status.
		 * If the user changes their MPD status it will toggle the visibility of the Supervisor fields.
		 *
		 * @param {Ext.form.FormPanel}	form_panel: 	The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Boolean}				spouse: 		A boolean variable that tells the handler if it is dealing with the user's fields or the spouse's fields
		 */
		onMpd: function(form_panel, mpd) {

			if (mpd) {
				form_panel.setMpdSupervisorVisable(true);
			} else {
				form_panel.setMpdSupervisorVisable(false);
			}

		},

		/**
		 * Handler for when the program varifies the user's marrital status.
		 * When it runs, it uses {@link tmn.view.TmnView#setSpouse} to propagate the marrital status through to all the Objects that need to know.
		 *
		 * @param {Boolean}				spouse 			A boolean variable that tells the handler if the user has a spouse or not.
		 */
		onSpouse: function (spouse) {
			if (spouse) {
				this.spouse = true;
				this.view.setSpouse(true);
			} else {
				this.spouse = false;
				this.view.setSpouse(false);
			}
		},

		/**
		 * Handler for when the user selects that they want to load a session into a financial details form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		onLoadSession: function(form_panel) {
			var dataObject	= {session_id:form_panel.getSelectedSession()};

			//if its an aussie form do appropriate checks before loading the session
			if (form_panel.aussie_form) {
				//check that it has been saved
				if (form_panel.isSaved() == true) {
					//load the session
					form_panel.onLoadSession(dataObject, false);
				//if not confirm that they want to load the session and lose there changes
				} else {
					Ext.MessageBox.confirm(
						'Warning',
						'Are you sure you want to load a new session without saving the last one?',
						function(btn) {
							if (btn == 'yes') {
								//if they want to load the session then do it
								this.form.onLoadSession(this.data, false);	//I can refer to this.form & this.data because the scope parameter below is set to an object containing those instance variables
							} else {
								this.form.setSelectedSession(this.form.getSession());
							}
						},
						{form:form_panel, data:dataObject} //this param sets the scope for the callback, I have set the scope as an object full of data I want to use in the callback
					);
				}
			}

			//if its an overseas form do appropriate checks before loading the session
			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form			= this.getForm('home-assignment');
				var international_assignment_form	= this.getForm('international-assignment');
				//create the data packet to be sent to the backend
				var dataObject	= {session_id:form_panel.getSelectedSession()};

				//if both are saved
				if (home_assignment_form.isSaved() && international_assignment_form.isSaved()) {
					//load the new session
					form_panel.onLoadSession(dataObject, false);
				//if not confirm that they want to load the session and lose there changes
				} else {
					Ext.MessageBox.confirm(
						'Warning',
						'You have unsaved changes on one of your pages. Are you sure you want to load a new session without saving the last one?',
						function(btn) {
							if (btn == 'yes') {
								//if they want to load the session then do it
								this.form.onLoadSession(this.data, false);	//I can refer to this.form & this.data because the scope parameter below is set to an object containing those instance variables
							} else {
								this.form.setSelectedSession(this.form.getSession());
							}
						},
						{form:form_panel, data:dataObject} //this param sets the scope for the callback, I have set the scope as an object full of data I want to use in the callback
					);
				}
			}
		},

		/**
		 * Handler for when the user selects that they want to load a session into a financial details form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 			The parameter to the request call.
		 */
		onLoadSessionSuccess: function(form_panel, response, options) {
			//parse repsonse
			var return_object	= Ext.util.JSON.decode(response.responseText),
				note			= '';

			if (form_panel.aussie_form) {
				//save the returned data
				this.financial_data[form_panel.id] = return_object.data;
				//load the data into the forms
				form_panel.onLoadSessionSuccess(this.financial_data[form_panel.id]);
				note = 'We take inflation to be 2.5% and so we multiply your stipend, additional tax, post tax super, pre tax super and mmrs by 1.025.';
			}

			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form			= this.getForm('home-assignment');
				var international_assignment_form	= this.getForm('international-assignment');

				//save the returned data
				this.financial_data[home_assignment_form.id]			= return_object.data['home-assignment'];
				this.financial_data[international_assignment_form.id]	= return_object.data['international-assignment'];

				//load the data into the forms
				home_assignment_form.onLoadSessionSuccess(this.financial_data[home_assignment_form.id]);
				international_assignment_form.onLoadSessionSuccess(this.financial_data[international_assignment_form.id]);
				note = 'We take inflation to be 2.5% and so we multiply your stipend, living away from home allowance, additional tax, post tax super, pre tax super and mmrs by 1.025.';
			}

			//if the data as had inflation applied by the back end tell the user and have the data reprocessed
			if (return_object.inflation_status !== undefined) {

				if (return_object.inflation_status == 'applied') {
					Ext.MessageBox.show({
						icon:		Ext.MessageBox.INFO,
						buttons:	Ext.MessageBox.OK,
						closable:	false,
						title:		'Inflation',
						msg:		'<b>We have increased your values for you to take inflation into account.<br />This has not been saved so if you want to go back just reload this session.</b><br /><br />'
							+'If you would like to view the this session <i>WITHOUT</i> inflation, follow this link <a href="http://mportal.ccca.org.au/TMN/viewer.php?session=' + form_panel.getSession() + '" target="_blank">http://mportal.ccca.org.au/TMN/viewer.php?session=' + form_panel.getSession() +'</a>.'
							+'<br /><br />Note: ' + note,
						scope:		form_panel,
						fn:			function() {
							if (this.locked) {
								Ext.MessageBox.show({
									icon: Ext.MessageBox.WARNING,
									buttons: Ext.MessageBox.OK,
									closable: false,
									title: 'Locked!',
									msg: "This session is Locked because it has been submitted. You can't save your changes to this session or delete this session. If you would like to save changes to this session, please use Save As to save a new version of the session."
								});
							}
						}
					});

					//send a financial data updated event so that the data will get sent to the server for reprocessing
					form_panel.fireEvent('financialdataupdated', form_panel, {isValid:function(){return true;}, getName:function(){return 'session_id';}}, form_panel.getSession(), true);
				} else if (return_object.inflation_status == 'needed') {
					Ext.MessageBox.show({
						icon:		Ext.MessageBox.INFO,
						buttons:	Ext.MessageBox.YESNO,
						closable:	false,
						title:		'Inflation',
						msg:		'<b>This session was created in a different Financial Year to this one. Due to inflation, the price of everything increases by about 2.5% each year.'
							+ ' Would you like us to automatically increase your values by 2.5%?</b><br /><br />'
							+ ' This will not be saved so if you want to go back just reload this session.<br /><br />'
							+ 'If you would like to preview your TMN <i>AFTER</i> inflation, follow this link <a href="http://mportal.ccca.org.au/TMN/viewer.php?isession=' + form_panel.getSession() + '" target="_blank">http://mportal.ccca.org.au/TMN/viewer.php?isession=' + form_panel.getSession() +'</a>.'
							+ '<br /><br />Note: ' + note,
						scope:		form_panel,
						fn:			function(button, options) {
							var dataObject	= {session_id:this.getSelectedSession()};

							if (button == 'yes') {
								this.onLoadSession(dataObject, true);
							} else {
								if (this.locked) {
									Ext.MessageBox.show({
										icon: Ext.MessageBox.WARNING,
										buttons: Ext.MessageBox.OK,
										closable: false,
										title: 'Locked!',
										msg: "This session is Locked because it has been submitted. You can't save your changes to this session or delete this session. If you would like to save changes to this session, please use Save As to save a new version of the session."
									});
								}
							}
						}
					});
				}
			}

		},

		/**
		 * Handler for when the user selects that they want to save a session into a financial details form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		onSaveSession: function(form_panel) {

			//if the session has never been saved before (ie the session id is not set) then call save as instead of save
			if (form_panel.getSession() == '' || form_panel.getSession() === undefined || form_panel.getSession() == null) {

				this.onSaveAsSession(form_panel);

			//otherwise do a normal save
			} else {

				//if this is a form just save the aussie form
				if (form_panel.aussie_form) {
					form_panel.onSaveSession(this.financial_data[form_panel.id]);
				}

				//if its an overseas form combine the data of the two sessions into an object and save that
				if (form_panel.overseas_form) {
					//grab the forms
					var home_assignment_form				= this.getForm('home-assignment');
					var international_assignment_form		= this.getForm('international-assignment');
					//save the returned data
					var dataObject	= {};
					dataObject['international-assignment']	= this.financial_data[international_assignment_form.id];
					//if the user has created data for the home assignment then save it otherwise save a copy of the international assignment
					if (this.financial_data[home_assignment_form.id] !== undefined) {
						dataObject['home-assignment']		= this.financial_data[home_assignment_form.id];
					} else {
						dataObject['home-assignment']		= this.financial_data[international_assignment_form.id];
					}

					//if the international end date is set copy it to the start date of the home assignment
					if (dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] !== undefined && dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] != null && dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] != '') {
						//set the home assignment start date to international end data
						dataObject['home-assignment']['OS_ASSIGNMENT_START_DATE'] = dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'];
					}

					//save the data for both forms (it doesn't matter which form you use)
					form_panel.onSaveSession(dataObject);
				}

			}

		},

		/**
		 * Handler for when the user selects that they want to save a session into a financial details form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		onSaveAsSession: function(form_panel) {

			var date			= new Date();
			var default_name	= date.format('Y-m-d H:i:s');

			//if a name exists for the current session then set the default name to that
			if (!(form_panel.getSessionName() === undefined || form_panel.getSessionName() == null)) {
				default_name	= form_panel.getSessionName() + " copy";
			}


			if (form_panel.aussie_form) {

				var dataObject = this.financial_data[form_panel.id];

				//remove session id, it is about to be copied and given a new id
				if (dataObject['session_id'] !== undefined) {
					delete dataObject['session_id'];
				}

				//remove auth session id, it is about to be copied and the new session will not have an auth session
				if (dataObject['auth_session_id'] !== undefined) {
					delete dataObject['auth_session_id'];
				}

				Ext.MessageBox.prompt(
						"Save As",
						"Give your session a name:",
						function(btn, text) {
							if (btn == 'ok') {
								if (text == '' || this.form.nameAlreadyExists(text)) {
									//If the user enters a name that already exists get them to try again
									Ext.MessageBox.show({
										icon: Ext.MessageBox.WARNING,
										buttons: Ext.MessageBox.OK,
										closable: false,
										title: 'Warning',
										msg: 'You entered a blank name or a name that already exists. Please enter another name.',
										scope: this,
										fn: function(btn, options) {
											this.controller.onSaveAsSession(this.form);
										}
									});
								} else {
									//set the session name
									this.form.setSessionName(text);
									this.data.session_name	= text;

									//create the session in the database
									this.form.onSaveAsSession(this.data);
								}
							}
						},
						{controller: this, form:form_panel, data:dataObject}, //this param sets the scope for the callback, I have set the scope as an object full of data I want to use in the callback
						false,
						default_name
				);

			} //eo aussie code


			if (form_panel.overseas_form) {

				//grab the forms
				var home_assignment_form					= this.getForm('home-assignment');
				var international_assignment_form			= this.getForm('international-assignment');

				//make sure there is data for international assignment before running through it
				if (this.financial_data[international_assignment_form.id]  !== undefined) {
					//remove session id, it is about to be copied and given a new id
					if (this.financial_data[international_assignment_form.id]['session_id'] !== undefined) {
						delete (this.financial_data[international_assignment_form.id]['session_id']);
					}

					//remove auth session id, it is about to be copied and the new session will not have an auth session
					if (this.financial_data[international_assignment_form.id]['auth_session_id'] !== undefined) {
						delete (this.financial_data[international_assignment_form.id]['auth_session_id']);
					}

					//remove home_assignment_session_id, it is about to be copied and the new session will have a new home assignment session
					if (this.financial_data[international_assignment_form.id]['home_assignment_session_id'] !== undefined) {
						delete (this.financial_data[international_assignment_form.id]['home_assignment_session_id']);
					}

					//remove international_assignment_session_id, it is about to be copied and the new session will have a new international assignment session
					if (this.financial_data[international_assignment_form.id]['international_assignment_session_id'] !== undefined) {
						delete (this.financial_data[international_assignment_form.id]['international_assignment_session_id']);
					}
				}

				var dataObject = {};
				 //use the international data because the user will be made to save for the first time on the international form
				//so save as will never have access to home assignment data as it won't yet exist
				dataObject['international-assignment']		= this.financial_data[international_assignment_form.id];
				//copy international data to home object
				dataObject['home-assignment']				= {};
				for ($field in dataObject['international-assignment']) {
					dataObject['home-assignment'][$field]	= dataObject['international-assignment'][$field];
				}

				//if the international end date is set copy it to the start date of the home assignment
				if (dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] !== undefined && dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] != null && dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'] != '') {
					//set the home assignment start date to international end data
					dataObject['home-assignment']['OS_ASSIGNMENT_START_DATE'] = dataObject['international-assignment']['OS_ASSIGNMENT_END_DATE'];
				}

				Ext.MessageBox.prompt(
						"Save As",
						"Give your session a name:",
						function(btn, text) {
							if (btn == 'ok') {
								if (text == '' || this.form.nameAlreadyExists(text)) {
									//If the user enters a name that already exists get them to try again
									Ext.MessageBox.show({
										icon: Ext.MessageBox.WARNING,
										buttons: Ext.MessageBox.OK,
										closable: false,
										title: 'Warning',
										msg: 'You entered a blank name or a name that already exists. Please enter another name.',
										scope: this,
										fn: function(btn, options) {
											this.controller.onSaveAsSession(this.form);
										}
									});
								} else {
									//set the session name for the forms
									this.home_form.setSessionName(text);
									this.international_form.setSessionName(text);
									this.data['home-assignment']['session_name'] = text;
									this.data['international-assignment']['session_name'] = text;

									//create the session in the database
									this.form.onSaveAsSession(this.data);
								}
							}
						},
						{controller: this, form: form_panel, home_form:home_assignment_form, international_form:international_assignment_form, data:dataObject}, //this param sets the scope for the callback, I have set the scope as an object full of data I want to use in the callback
						false,
						default_name
				);

			} //eo overseas code

		},

		/**
		 * Handler for when the user selects that they want to save a new session and it succeeds form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 			The parameter to the request call.
		 */
		onSaveAsSessionSuccess: function(form_panel, response, options) {
			//parse repsonse
			var return_object				= Ext.util.JSON.decode(response.responseText);
			var data						= return_object['data'];

			if (form_panel.aussie_form) {
				if (data !== undefined) {
					if (data['session_id']) {
						form_panel.setSession(data['session_id']);
						form_panel.saveAsInternalTransfers(data['session_id']);
						form_panel.reloadSessionCombo();
						form_panel.onSaveAsSessionSuccess();
					}
				}
			}


			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form			= this.getForm('home-assignment');
				var international_assignment_form	= this.getForm('international-assignment');
				//grab the returned data
				var data							= return_object.data;

				if (data !== undefined) {

					//grab data for each of the forms
					var home_assignment_data				= data['home-assignment'];
					var international_assignment_data		= data['international-assignment'];

					//if a session id was returned for home assignment set it
					if (home_assignment_data['session_id']) {
						//set the new session id
						home_assignment_form.setSession(home_assignment_data['session_id']);
						//reload the forms session combo
						home_assignment_form.reloadSessionCombo();
						//let the form deal with the newly saved session
						home_assignment_form.onSaveAsSessionSuccess(home_assignment_data['session_id']);
					}

					//if a session id was returned for international assignment set it
					if (international_assignment_data['session_id']) {
						//set the new session id
						international_assignment_form.setSession(international_assignment_data['session_id']);
						//reload the forms session combo
						international_assignment_form.reloadSessionCombo();
						//let the form deal with the newly saved session
						international_assignment_form.onSaveAsSessionSuccess(international_assignment_data['session_id']);
					}

					form_panel.saveAsInternalTransfers(international_assignment_data['session_id']);
				}

			}

		},

		/**
		 * Handler for when the user selects that they want to delete a session into a financial details form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		onDeleteSession: function(form_panel) {

			var dataObject	= {};

			//if there is no session to delete then show a warning and don't proceed any further
			if (form_panel.getSession() == '' || form_panel.getSession() == null || form_panel.getSession() === undefined) {
				Ext.MessageBox.alert('Warning', 'A Saved Session needs to be loaded for the delete function to work. If you have no Sessions available in the combo box there is no need to delete.');
				return;
			}

			if (form_panel.aussie_form) {
				//create the data object filled with the session id's to be deleted
				dataObject.session_id					= form_panel.getSession();
			}

			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form				= this.getForm('home-assignment');
				var international_assignment_form		= this.getForm('international-assignment');

				//create the data object filled with the session id's to be deleted
				dataObject['home-assignment']			= {session_id: home_assignment_form.getSession()};
				dataObject['international-assignment']	= {session_id: international_assignment_form.getSession()};
			}

			Ext.MessageBox.confirm(
					'Warning',
					'Are you sure you want to delete this session?',
					function(btn) {
						if (btn == 'yes') {
							this.form.onDeleteSession(this.data);
						}
					},
					{form:form_panel, data:dataObject} //this param sets the scope for the callback, I have set the scope as an object full of data I want to use in the callback
			);
		},

		/**
		 * Handler for when the user selects that they want to delete a session and it succeeds form (done using {@link tmn.view.FinancialDetailsForm}).
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 			The parameter to the request call.
		 */
		onDeleteSessionSuccess: function(form_panel, response, options) {

			if (form_panel.aussie_form) {
				//get form to clean itself up
				form_panel.onDeleteSessionSuccess();
			}

			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form			= this.getForm('home-assignment');
				var international_assignment_form	= this.getForm('international-assignment');

				//get form's to clean themselves up
				home_assignment_form.onDeleteSessionSuccess();
				international_assignment_form.onDeleteSessionSuccess();

				//if the user is on the home assignment form then move them back to the international assignment form
				if (form_panel.home_assignment) {
					this.onPrevious();
				}
			}

		},

		/**
		 * Clears the form or forms (for an overseas missionary) of all data associated with the current session
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		onResetSession: function(form_panel) {

			if (form_panel.aussie_form) {
				//if the form has unsaved changes check whether they want to save first
				if (!form_panel.isSaved()) {
					Ext.MessageBox.confirm(
							'Warning',
							'Are you sure you want to clear your changes to this session?',
							function(btn) {
								if (btn == 'yes') {
									//when they confirm get form to clean itself up
									this.resetForm();
								}
							},
							form_panel
					);

				//otherwise just get form to clean itself up
				} else {
					form_panel.resetForm();
				}
			}

			if (form_panel.overseas_form) {
				//grab the forms
				var home_assignment_form			= this.getForm('home-assignment');
				var international_assignment_form	= this.getForm('international-assignment');

				//if the form has unsaved changes check whether they want to save first
				if (!home_assignment_form.isSaved() || !international_assignment_form.isSaved()) {
					Ext.MessageBox.confirm(
							'Warning',
							'Are you sure you want to clear your changes to this session?',
							function(btn) {
								if (btn == 'yes') {
									//get form's to clean themselves up
									this.home.resetForm();
									this.international.resetForm();

									//if the user is on the home assignment form then move them back to the international assignment form
									if (this.form.home_assignment) {
										this.controller.onPrevious();
									}
								}
							},
							{controller:this, form:form_panel, home:home_assignment_form, international:international_assignment_form} //set scope to an object that contains the data the callback needs
					);

				//otherwise just get form to clean itself up
				} else {
					//get form's to clean themselves up
					home_assignment_form.resetForm();
					international_assignment_form.resetForm();

					//if the user is on the home assignment form then move them back to the international assignment form
					if (form_panel.home_assignment) {
						this.onPrevious();
					}
				}

			}
		},

		/**
		 * Replaces financial_data with empty object for a particular form
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		resetFinancialData: function(form_panel) {
			this.financial_data[form_panel.id] = {};
		},

		/**
		 * Replaces financial_data with empty object for a particular form
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})
		 */
		resetResponse: function(form_panel) {
			this.response = {};
		},

		/**
		 * Handler for when the user updates a piece of financial data (done using {@link tmn.view.FinancialDetailsForm}) and it needs to be processed.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the complete panel which also contains the form (see {@link Ext.form.FormPanel})<br />
		 * @param {Mixed} 				field: 				The Object that represents the field that was just updated (needs to have an isValid() and a getName() method)<br />
		 * @param {Mixed} 				newVal: 			The new value of the field that was just updated<br />
		 * @param {Boolean}				send_ajax_request: 	Tells the method whether it should send the financial data (with this new change) to the server for processing
		 * 													or if it should be stored locally and processed later (Storing locally mostly happens when the form is being loaded because
		 * 													this handler is called repeatedly to load the default values and you don't want the system to get bogged down with
		 * 													unnessisary ajax requests).
		 */
		processFinancialData: function(form_panel, field, newVal, send_ajax_request) {

			if (field.isValid())	//ignore any invalid fields
			{
				if (this.financial_data[form_panel.id] === undefined) {			//if the form has never sent data to be processed
					this.financial_data[form_panel.id] = {};					//create an associative array to store the data for this form
				}
				this.financial_data[form_panel.id][field.getName()] = newVal;	//add the new value to this forms financial data array
				this.financial_data[form_panel.id]['overseas'] = this.overseas;	//add the misso's other status info
				this.financial_data[form_panel.id]['spouse'] = this.spouse;
				this.financial_data[form_panel.id]['home_assignment'] = form_panel.home_assignment;

				if (send_ajax_request == true) {
					Ext.Ajax.request({											//send all the data about the misso to the server for processing
						url: 'php/cookie_monster.php',
						params: {financial_data: Ext.util.JSON.encode(this.financial_data[form_panel.id])},
						success: this.onProcessFinancialDataSuccess.createDelegate(this, [form_panel], 0),
						failure: this.onProcessFinancialDataFailure.createDelegate(this, [form_panel], 0),
						scope: this
					});
				}
			}
		},

		/**
		 * Handler for when the user updates a piece of financial data (done using {@link tmn.view.FinancialDetailsForm}) and that data is successfully processed.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the form that triggered the update. (the object represents the form and its containing panel)<br />
		 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 			The parameter to the request call.
		 */
		onProcessFinancialDataSuccess: function(form_panel, response, options) {
			var return_object = Ext.util.JSON.decode(response.responseText);									//decode the response

			if (return_object.success == true || return_object.success == 'true'){								//check if it succeeded or not
				delete (this.financial_data[form_panel.id]);													//remove the previous set of financial data
				this.financial_data[form_panel.id] = return_object.financial_data;								//save the processed data in it place
			}

			form_panel.onProcessFinancialDataSuccess(this.financial_data[form_panel.id], response, options);	//do local processing (will handle both success and error)
		},

		/**
		 * Handler for when the user updates a piece of financial data (done using {@link tmn.view.FinancialDetailsForm}) and that data fails to be processed.
		 *
		 * @param {Ext.form.FormPanel} 	form_panel: 		The Object that represents the form that triggered the update. (the object represents the form and its containing panel)<br />
		 * @param {Object} 				response: 			The XMLHttpRequest object containing the response data. (see ,<a href="http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface">
		 * 													http://www.w3.org/TR/XMLHttpRequest/#the-xmlhttprequest-interface</a> if you don't know what a XMLHttpRequest contains)<br />
		 * @param {Object} 				options: 			The parameter to the request call.
		 */
		onProcessFinancialDataFailure: function(form_panel, response, options) {
			form_panel.onProcessFinancialDataFailure(response, options);
		},

		/**
		 * Handler for when the user has successfully finished submitting their TMN. It will refresh stuff so that the user can't go back and change anything.
		 */
		onTmnSubmitted: function() {
			Ext.each([this.getForm('aussie-based'), this.getForm('home-assignment'), this.getForm('international-assignment')], function(form, index, allForms) {
				form.resetForm();
				form.reloadSessionCombo();
			}, this);
		},

		/**
		 * Handler for when the user clicks the forward or back buttons on the browser.
		 * Look at {@link #onPrevious} and {@link #onSubmitSuccess} for the other lines of code that make the history mangement work.
		 * Also look at {@link Ext.History} for examples on how to use it.
		 *
		 * @param {String}				token: 			String that identifies the active state of the History stack (after the user has gone back). In our case this is the index of the form.
		 */
		onHistoryChange: function(token){
			if (token){
				index = parseInt(token);
				if (index < this.view.active) {				//if the user hit back
					if (this.overseas == true) {
						if (this.view.length == 1){
							this.view.disablePrevious();
							this.view.disableNext();
						} else {
							if (index == this.view.indexOfFirstOverseasForm()) {					//does bound checking on the index
								this.view.disablePrevious();	//if it is the minimum bound, disable the previous button
								this.view.enableNext();
							} else {
								this.view.enablePrevious();		//if it is a regular index enable buttons
								this.view.enableNext();
							}
						}
					} else {
						if (this.view.length == 1){
							this.view.disablePrevious();
							this.view.disableNext();
						} else {
							if (index == this.view.indexOfFirstAussieForm()) {					//does bound checking on the index
								this.view.disablePrevious();	//if it is the minimum bound, disable the previous button
								this.view.enableNext();
							} else {
								this.view.enablePrevious();		//if it is a regular index enable buttons
								this.view.enableNext();
							}
						}
					}
					this.view.changeNextText('Next');	//make sure the buttons say the right thing
					this.view.changeForm(index);		//hides the current form and shows the form with the index we just passed it
				} else if (index > this.view.active) {	//if the user hit forward
					this.onNext();
					return true;
				}
			} else {
				//If the user is going to leave the page, show a confirmation box
				Ext.MessageBox.show({
					animEl: this.view.header,
					icon: Ext.MessageBox.WARNING,
					buttons: Ext.MessageBox.YESNO,
					closable: false,
					title: 'Warning!',
					msg: 'If you click back again, You will leave the TMN and lose your data!<br />Is this what you want to do?',
					scope: this,
					fn: function(buttonId, opt) {
						if (buttonId == 'yes') {
							Ext.History.back();
						}
						if (buttonId == 'no') {
							Ext.History.forward();
						}
					}
				});
			}
		},

		/**
		 * Resizes the view's height to the height of the avaiable space in the browser.
		 */
		doResize: function() {
            var windowHeight = Ext.getDoc().getViewSize(false).height;

            var warnEl = Ext.get('fb');
            var warnHeight	= warnEl ? warnEl.getHeight() : 0;

            var availHeight	= windowHeight - 14 - warnHeight;
            var minHeight	= 400;
            availHeight		= (availHeight > minHeight) ? availHeight : minHeight;

            this.view.setHeight(availHeight);
        },

		/**
		 * Initialises the TmnController. This is where event handlers are registered.<br />See {@link tmn.view.TmnView#on}
		 * to learn how to register a handler with an event.<br />
		 * It also creates the view, hides the loading mask, sets the quick tip defaults and sets up the history management.
		 */
		init: function() {
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');

			////////////////Quick Tip Stuff///////////////////
			Ext.QuickTips.init();							// Enables quick tips and validation messages
			Ext.apply(Ext.QuickTips.getQuickTip(), {		// Quicktip defaults
			    showDelay: 250,
			    dismissDelay: 0,
			    hideDelay: 2000,
			    trackMouse: false
			});
			Ext.form.Field.prototype.msgTarget = 'side';	// Puts validation messages on the side

			///////////////Init History//////////////////////
			Ext.History.init();
			Ext.History.add("0");
			//manage change in history
			Ext.History.on('change', this.onHistoryChange, this);

			//create view
			this.view = new tmn.view.TmnView;

			///////////////Init Resize//////////////////////

	        // Resize on demand
	        Ext.EventManager.onWindowResize(this.doResize, this);

	        //resize the form
	        this.doResize();

			//register event handlers (see the API doc for tmn.view.TmnView.on() to find out how to do this )
				//view events
			this.view.on('next', this.onNext, this);
			this.view.on('previous', this.onPrevious, this);

				//form_panel events (applies to all forms PersonalDetails, FiancialDetails, ... )
			this.view.on('loadsuccess', this.onLoadSuccess, this);
			this.view.on('loadfailure', this.onLoadFailure, this);
			this.view.on('submitsuccess', this.onSubmitSuccess, this);
			this.view.on('submitfailure', this.onSubmitFailure, this);

				//PersonalDetailsForm events
			this.view.on('single', this.onSpouse.createDelegate(this, [false]), this);			//these use createDelegate to send extra parmeters to the handler (ie spouse true or false)
			this.view.on('married', this.onSpouse.createDelegate(this, [true]), this);
			this.view.on('aussie', this.onOverseas.createDelegate(this, [false], true), this);
			this.view.on('overseas', this.onOverseas.createDelegate(this, [true], true), this);
			this.view.on('fulltime', this.onFullTime, this);
			this.view.on('parttime', this.onPartTime, this);
			this.view.on('mpdyes', this.onMpd.createDelegate(this, [true], true), this);
			this.view.on('mpdno', this.onMpd.createDelegate(this, [false], true), this);

				//FinancialDetailsForm events
			this.view.on('resetfinancialdata', this.resetFinancialData, this);
			this.view.on('financialdataupdated', this.processFinancialData, this);
			this.view.on('loadsession', this.onLoadSession, this);
			this.view.on('loadsessionsuccess', this.onLoadSessionSuccess, this);
			this.view.on('savesession', this.onSaveSession, this);
			this.view.on('saveassession', this.onSaveAsSession, this);
			this.view.on('saveassessionsuccess', this.onSaveAsSessionSuccess, this);
			this.view.on('deletesession', this.onDeleteSession, this);
			this.view.on('deletesessionsuccess', this.onDeleteSessionSuccess, this);
			this.view.on('resetsession', this.onResetSession, this);

				//PrintForm events
			this.view.on('tmnsubmitted', this.onTmnSubmitted, this);

			//load initial form
			this.view.loadActiveForm();

			////////////////Loading Message Stuff///////////////
			//  Hide loading message
			loading.fadeOut({ duration: 0.2, remove: true });
			//  Hide loading mask
			loadingMask.setOpacity(1.0);
			loadingMask.shift({
				xy: loading.getXY(),
				width: loading.getWidth(),
				height: loading.getHeight(),
				remove: true,
				duration: 1.1,
				opacity: 0.1,
				easing: 'easeOut'
			});
		}
	};

}(); //make imediate call to function so that TmnController contains the return object

//this call will initalise the tmn for use when the browser is ready.
//onReady is used for IE's sake. IE needs you to wait till its finished doing whatever IE does (holding back the internet, etc) before you can run your code.
Ext.onReady(tmn.TmnController.init, tmn.TmnController);
