Ext.ns('tmn');

tmn.viewer = function() {

	return {
		session: '',
		
		controlpanel: new Ext.form.FormPanel({
			title: 'Controls',
			frame: true,
			layout: 'column',
			bodyStyle: 'padding:5px',
			items: [
				{
					layout: 'form',
					columnWidth:.5,
					items: [
						{
						    id: 'session',
				        	xtype: 'combo',
						    fieldLabel: 'Session',
						    hiddenName: 'SESSION',
						    hiddenId: 'SESSION_hidden',
						    triggerAction: 'all',
				        	editable: false,
						    forceSelection: true,
						    allowBlank:false,
						    
						    mode: 'local',
						    // store getting items from server
						    store: new Ext.data.JsonStore({
						        itemId:'session_store',
						        root: 'data',
						        fields:['SESSION_ID', 'SESSION_NAME'],
						        url:'./php/auth/authviewer.php',
						        autoLoad: {
						        	params: { mode: 'load' }
						        }
						    }),
						    
						    valueField: 'SESSION_ID',
							displayField:'SESSION_ID',
						    tpl: '<tpl for=\".\"><div class=\"x-combo-list-item\">{SESSION_ID}, {SESSION_NAME}</div></tpl>',
						    listeners: {
						    	select: function(combo, record, index) {
						    		
						    		tmn.viewer.session = record.get('SESSION_ID');
						    		Ext.getCmp('confirm').enable();
						    		Ext.getCmp('reject').enable();
						    		
						    		Ext.Ajax.request({
										url: './php/auth/authviewer.php',
						    			scope: tmn.viewer,
										params: {
											mode: 'get',
											session: record.get('SESSION_ID')
										},
										success: tmn.viewer.display,
										failure: this.fail
									});
						    	}	
						    }
						}
					]
				},
				{
					layout: 'form',
					columnWidth:.25,
					items: [
						{
						    id: 'confirm',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'Confirm this TMN Session',
						    width: 100,
						    handler: function(button, event) {
								if (tmn.viewer.session != '') {
									Ext.Ajax.request({
										url: './php/auth/authprocessor.php',
										scope: tmn.viewer,
										params: {
											response: 'Yes',
											session: tmn.viewer.session
										},
										success: function() {
											Ext.MessageBox.show({
												icon: Ext.MessageBox.ERROR,
												buttons: Ext.MessageBox.OK,
												closable: false,
												title: 'Success!',
												msg: 'This Session was successfully Confirmed.'
											});
										},
										failure: this.fail
									});
								}
						    }
						}
					]
				},
				{
					layout: 'form',
					columnWidth:.25,
					items: [
						{
						    id: 'reject',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'Reject this TMN Session',
						    width: 100,
						    handler: function(button, event) {
								Ext.Ajax.request({
									url: './php/auth/authprocessor.php',
									scope: tmn.viewer,
									params: {
										response: 'No',
										session: tmn.viewer.session
									},
									success: function() {
										Ext.MessageBox.show({
											icon: Ext.MessageBox.ERROR,
											buttons: Ext.MessageBox.OK,
											closable: false,
											title: 'Success!',
											msg: 'This Session was successfully Rejected.'
										});
									},
									failure: this.fail
								});
						    }
						}
					]
				}
			]
		}),
		
		reasonpanel: new Ext.Panel({
			header:		false,
			frame:		true,
			bodyStyle:	'padding:0px'
		}),
		
		fail: function() {
			Ext.MessageBox.show({
				icon: Ext.MessageBox.ERROR,
				buttons: Ext.MessageBox.OK,
				closable: false,
				title: 'Error!',
				msg: 'There was an error processing your request. Please try again.'
			});
		},
		
		display: function(response, options){
			var responseObj = Ext.util.JSON.decode(response.responseText),
				json		= responseObj['tmn_data'],
				session		= options.params.session,
				isOverseas	= false,
				hasSpouse	= false;
				
			if (json['aussie-based'] !== undefined)	{						//if its the aussie based only version of the tmn
				isOverseas	= false;
				hasSpouse	= (json['aussie-based']['s_firstname'] !== undefined ? true : false);
			} else {
				isOverseas	= true;
				hasSpouse	= (json['international-assignment']['s_firstname'] !== undefined ? true : false);
			}
			
			Ext.getCmp('confirm').enable();
			
			//TODO: display auth reasons in auth panel
			
			this.view.renderSummary(json, isOverseas, hasSpouse);
				
		},
		
		init: function() {
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');
			
			//create view
			this.view = new tmn.view.SummaryPanel;
			
			this.controlpanel.setWidth(900);
			this.controlpanel.render('tmn-viewer-controls-cont');
			
			this.reasonpanel.setWidth(900);
			this.reasonpanel.render('tmn-reasonpanel-cont');
			
			this.view.setWidth(900);
			this.view.render('tmn-viewer-cont');
			
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

}();

Ext.onReady(tmn.viewer.init, tmn.viewer);