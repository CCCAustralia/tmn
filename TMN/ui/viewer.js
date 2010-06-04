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
						        url:'php/tmn-viewer-backend.php',
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
						    		Ext.getCmp('reprocess').enable();
						    		Ext.getCmp('print').enable();
						    		
						    		Ext.Ajax.request({
										url: 'php/tmn-viewer-backend.php',
						    			scope: tmn.viewer,
										params: {
											mode: 'get',
											session: record.get('SESSION_ID')
										},
										callback: tmn.viewer.display
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
						    id: 'reprocess',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'View This TMN Reprocessed',
						    width: 100,
						    handler: function(button, event) {
						    	if(button.getText() == 'View This TMN Reprocessed') {
									if (tmn.viewer.session != '') {
										button.setText('View Original');
										Ext.Ajax.request({
											url: 'php/tmn-viewer-backend.php',
							    			scope: tmn.viewer,
											params: {
												mode: 'reprocess',
												session: tmn.viewer.session
											},
											callback: tmn.viewer.display
										});
									}
						    	} else {
									
									if (tmn.viewer.session != '') {
						    			button.setText('View This TMN Reprocessed');
										Ext.Ajax.request({
											url: 'php/tmn-viewer-backend.php',
							    			scope: tmn.viewer,
											params: {
												mode: 'get',
												session: tmn.viewer.session
											},
											callback: tmn.viewer.display
										});
									}
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
						    id: 'print',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'Print',
						    width: 100,
						    handler: function(button, event) {
								Ext.ux.Printer.print(tmn.viewer.view);
						    }
						}
					]
				}
			]
		}),
		
		display: function(options, success, response){
			var responseObj = Ext.util.JSON.decode(response.responseText);
			var json;
			
			if (responseObj.success == true || responseObj.success == 'true') {
				json = responseObj['tmn_data'];
				
				this.view.setSession(options.params.session);
				
				if (json['success'] === undefined){	//if its the overseas version of the tmn
					if (json['aussie-based'] !== undefined) {
						this.view.setOverseas(false);
						if (json['aussie-based']['s_firstname'] === undefined) {
							this.view.setSpouse(false);
						} else {
							this.view.setSpouse(true);
						}
						
						this.view.values['aussie-based'] = json['aussie-based'];
					} else {
						this.view.setOverseas(true);
						if (json['international-assignment']['s_firstname'] === undefined) {
							this.view.setSpouse(false);
						} else {
							this.view.setSpouse(true);
						}
						
						
						this.view.values['international-assignment'] = json['international-assignment'];
						this.view.values['home-assignment'] = json['home-assignment'];
					}
				} else {							//if its the aussie based only version of the tmn
					json = json['tmn_data'];
					this.view.setOverseas(false);
					if (json['s_firstname'] === undefined) {
						this.view.setSpouse(false);
					} else {
						this.view.setSpouse(true);
					}
					
					this.view.values['aussie-based'] = json;
				}
				
				this.view.loadForm();
				
			} else {
				
				if (responseObj['errors'] !== undefined) {
					//tell the user they don't have permission to access this
					Ext.MessageBox.show({
						icon: Ext.MessageBox.ERROR,
						buttons: Ext.MessageBox.OK,
						closable: false,
						title: 'Error',
						msg: 'The TMN reprocessed with errors and cannot be viewed. Contact <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a> for a solution.'
					});
				} else {
					//tell the user they don't have permission to access this
					Ext.MessageBox.show({
						icon: Ext.MessageBox.ERROR,
						buttons: Ext.MessageBox.OK,
						closable: false,
						title: 'Error',
						msg: 'You don\'t have access to this information. It either doesn\'t exist or you don\'t have permission to see it. If you think this is incorrect please contact <a href="mailto:tech.team@ccca.org.au">tech.team@ccca.org.au</a>.'
					});
				}
				
				//change text on reprocess button back to what it was
				if(Ext.getCmp('reprocess').getText() == 'View This TMN Reprocessed') {
					Ext.getCmp('reprocess').setText('View Original');
		    	} else {
		    		Ext.getCmp('reprocess').setText('View This TMN Reprocessed');
		    	}
			}
		},
		
		init: function() {
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');
			
			//create view
			this.view = new tmn.view.PrintForm;
			
			this.controlpanel.setWidth(900);
			this.controlpanel.render('tmn-viewer-controls-cont');
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