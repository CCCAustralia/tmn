

Ext.ns('TMN');

TMN.Form /*change Form to appropriate name*/ = Ext.extend(Ext.form.FormPanel, {
	
	id: '', //enter id
	frame: true,
	title: '', //enter title
	url: '', //enter url (for load and submit)
		
	initComponent: function() {
		
		var config = {
			defaultType: 'textfield',
			monitorValid: true,
			
			items:[
				//add items here
			],
			bbar: new Ext.ux.StatusBar({
				defaultText: 'Ready',
				plugins: new Ext.ux.ValidationStatus({form:this.id}) //change test_form to id from above (ln 6)
			})
		};
		
		Ext.apply(this, Ext.apply(this.initialConfig, config));
		TMN.Form.superclass.initComponent.apply(this, arguments); // change Form to match above name (ln 4)
	}, //eo initForm
	
	//required function do not edit unless you know what you are doing
	loadForm: function (successCallback, failureCallback) {
		this.load({
			url: this.url,
            params:{mode:'get'},
			success: function (form, action) {
				if (successCallback !== undefined) successCallback();
			},
			failure: function (form, action) {
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
				}
				if (failureCallback !== undefined) failureCallback();
			}
		})
	},
	
	//required function do not edit unless you know what you are doing
	submitForm: function (successCallback, failureCallback) {
		this.form.submit({
			url: this.url,
        	params:{mode:'set'},
			success: function (form, action) {
				if (successCallback !== undefined) successCallback();
			},
			failure: function (form, action) {
				if (action.failureType === Ext.form.Action.CONNECT_FAILURE) {
					Ext.MessageBox.alert('Server Error', 'Could Not Connect to Server! Please Contact The Technology Team at tech.team@ccca.org.au');
				}
				if (failureCallback !== undefined) failureCallback();
			}
		});
	}
	
}); //eo extend

Ext.reg('test_form', TMN.Form); //change test_form to appropriate name and change Form to match above name (ln 4)

