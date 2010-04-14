
Ext.BLANK_IMAGE_URL = 'lib/resources/images/default/s.gif';

Ext.ns('TMN');

TMN.TmnContainer = Ext.extend(Ext.Panel, {
	
	id: 'tmn_container',
	frame: true,
	header: false,
	renderTo: 'tmn-cont',
	active: 0,
	valid: true,
	//floating: true,
	listeners: {
		afterRender: function() {
			//grab guid
			var cookies = getcookie();
			this.guid = cookies['guid'];
			//TODO once sessions implemented set to session id
			this.session = cookies['guid'];
			
			//disable buttons till the form has loaded
			this.buttonsDisabled(true);
			this.items.items[this.active].set_guid_session(this.guid, this.session);
			this.items.items[this.active].loadForm(this.buttonsDisabled.createDelegate(this, [false]), this.buttonsDisabled.createDelegate(this, [false]));
			for ( itemCount=1; itemCount < this.items.getCount(); itemCount++)
			{
				this.items.items[itemCount].hide();
				this.items.items[itemCount].set_guid_session(this.guid, this.session);
			}
		}
	},
		
	initComponent: function() {
		
		var config = {
			
			width:900,
			
			bbar: [
					{
						id: 'prev_button',
						text: 'Previous',
						handler: this.navHandler.createDelegate(this, [-1]),
						disabled: true
					},'->',{
						id: 'next_button',
						text: 'Next',
						handler: this.navHandler.createDelegate(this, [1])
					}
			],
			
			items:[
				new TMN.PersonalDetails, {xtype: 'financialdetailsform'},  {xtype: 'view_tmn'}
			]
		
		};
		
		//apply config to the initialConfig of FormPanel
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		
		//call parent
		TMN.TmnContainer.superclass.initComponent.apply(this, arguments);
		
	}, //eo initComponent
	
	navHandler: function(direction) {
		//grab the currently active form (old_form) and the next form to be active (next_form)
		var active_form = this.items.items[this.active];
		
		//while submition happens don't allow navigation
		this.buttonsDisabled(true);
		
		//if moving foward submit the form and move to the next form otherwise just move to the previous form without submition
		if (direction > 0) {
			//submit the active form (send it a function to run if it is successful)
			active_form.submitForm(this.changeForm.createDelegate(this, [direction]), this.buttonsDisabled.createDelegate(this, [false]));
		} else {
			//move to the previous form
			this.changeForm.call(this, direction);
		}
	},
	
	changeForm: function(direction){
		
		//grab the currently active form (old_form) and the next form to be active (next_form)
		var active_form = this.items.items[this.active];
		this.active += direction;
		var next_form = this.items.items[this.active];
		
/************ slideOut slideIn
		var o = 'r', i = 'l';
			
		//swap form that is shown
		if (direction < 0) {o='l'; i='r'}
		active_form.el.slideOut(o,{callback: function (active_form, next_form) {
			active_form.hide();
			next_form.show();
			this.doLayout();
			next_form.el.slideIn(i);
		}.createDelegate(this, [active_form, next_form])});
*************/

		active_form.el.fadeOut({callback: function (active_form, next_form) {
			active_form.hide();
			next_form.show();
			this.doLayout();
			next_form.el.fadeIn();
		}.createDelegate(this, [active_form, next_form])});

		//pass the response text from processing the financial details to view tmn for display
		if(Ext.getCmp('financial_details').response !== undefined) Ext.getCmp('view_panel').response = Ext.getCmp('financial_details').response;

		//load form that has just been shown
		next_form.loadForm(this.buttonsDisabled.createDelegate(this, [false]), this.buttonsDisabled.createDelegate(this, [false]));
	},
	
	buttonsDisabled: function (disabled) {
		
		if (disabled == true){
			Ext.getCmp('prev_button').setDisabled(true);
			Ext.getCmp('next_button').setDisabled(true);
		} else {
			// do bounds checking on the containers items and set the buttons disabled status
	/****** Change absolute references to local references in next version *******/
			if (this.active == this.items.getCount()-1) {
				Ext.getCmp('prev_button').setDisabled(false);
				Ext.getCmp('next_button').setDisabled(false);
				Ext.getCmp('next_button').setText('Print');
			} else if (this.active == 0) {
				Ext.getCmp('next_button').setDisabled(false);
				Ext.getCmp('prev_button').setDisabled(true);
				Ext.getCmp('next_button').setText('Next');
			} else {
				Ext.getCmp('prev_button').setDisabled(false);
				Ext.getCmp('next_button').setDisabled(false);
				Ext.getCmp('next_button').setText('Next');
			}
		}
		
	}
	
}); //eo extend


Ext.onReady(function()
{
	// Enables validation messages and puts them on the side
	Ext.QuickTips.init();
	
	// Quicktip defaults
	Ext.apply(Ext.QuickTips.getQuickTip(), {
	    showDelay: 250,
	    dismissDelay: 0,
	    hideDelay: 2000,
	    trackMouse: false
	});
	
	Ext.form.Field.prototype.msgTarget = 'side';
	
	var tmn = new TMN.TmnContainer;

}); //eo onReady

