Ext.ns('tmn');

tmn.viewer = function() {

	return {
		session: '',
		
		controlpanel: new Ext.form.FormPanel({
			title: 'Controls',
			frame: true,
			layout: 'column',
			bodyStyle: 'padding:5px 5px 0px 5px',
			items: [
				{
					layout: 'form',
					columnWidth:.35,
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
						    store: this.sessionStore,
						    
						    valueField: 'SESSION_ID',
							displayField:'SESSION_NAME',
						    tpl: '<tpl for=\".\"><div class=\"x-combo-list-item\">{SESSION_ID}, {SESSION_NAME} - created by {FIRSTNAME} {SURNAME}</div></tpl>',
						    listeners: {
						    	scope:	tmn.viewer,
						    	select: function(combo, record, index) {
						    		
						    		this.session	= record.get('SESSION_ID');
						    		this.getForm().items.map['email'].setNameAndEmail(record.get('FIRSTNAME'), record.get('SURNAME'), record.get('EMAIL'));
						    		
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
					columnWidth:.125,
					items: [
						{
						    id: 'confirm',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'Approve this TMN',
						    width: 80,
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
					columnWidth:.125,
					items: [
						{
						    id: 'reject',
				        	xtype: 'button',
				        	disabled: true,
						    text: 'Reject this TMN',
						    width: 80,
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
				},
				{
					layout: 'form',
					columnWidth:.25,
					items: [
					    {
					    	xtype:	'label',
					    	text:	'Want to talk before approving?:',
					    	width:	100
					    }
					]
				},
				{
					layout: 'form',
					columnWidth:.15,
					items: [
						{
						    id:			'email',
				        	xtype:		'linkbutton',
				        	disabled:	true,
						    text:		'Email Creator',
						    href:		'mailto:tech.team@ccca.org.au'
						}
					]
				},
				{
					  xtype: 'box',
					  columnWidth:1,
					  autoEl: {tag: 'center', html: '<div id="tmn-authviewer-overall-status" class=""><span id="tmn-authviewer-overall-status-label">Overall Status: </span><span id="tmn-authviewer-overall-status-status" style="color:#999999;">Awaiting Approval</span></div>'}
				}

			]
		}),
		
		reasonpanel: new tmn.view.AuthorisationPanel(this.view, {id: 	'reasonpanel', noNames: true}),
		
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
				progress	= responseObj['progress'],
				auth		= responseObj['authoriser'],
				authReasons	= auth['reasons'],
				authResponse= auth['response'],
				json		= responseObj['data'],
				session		= options.params.session,
				isOverseas	= false,
				hasSpouse	= false,
				statusEl	= Ext.get('tmn-authviewer-overall-status-status');
				
			if (json['aussie-based'] !== undefined)	{						//if its the aussie based only version of the tmn
				isOverseas	= false;
				hasSpouse	= (json['aussie-based']['s_firstname'] !== undefined ? true : false);
			} else {
				isOverseas	= true;
				hasSpouse	= (json['international-assignment']['s_firstname'] !== undefined ? true : false);
			}
			
			//render reasons
			if (authReasons['total'] > 0) {
				this.reasonpanel.showPanel(authReasons);
			} else {
				this.reasonpanel.hidePanel();
			}
			
			//Change interface based on 
			if (authResponse == 'Yes') {
				Ext.getCmp('confirm').setText('You Confirmed This Session');
	    		Ext.getCmp('confirm').disable();
	    		Ext.getCmp('reject').setText('Reject this TMN Session')
	    		Ext.getCmp('reject').enable();
			} else if (authResponse == 'No') {
				Ext.getCmp('confirm').setText('Confirm this TMN Session');
				Ext.getCmp('confirm').enable()
				Ext.getCmp('reject').setText('You Rejected This Session');
	    		Ext.getCmp('reject').disable();
			} else {
				Ext.getCmp('confirm').enable();
	    		Ext.getCmp('reject').enable();
			}
			
			//set overall status
			if (progress == 'Yes') {
				Ext.getCmp('confirm').disable();
	    		Ext.getCmp('reject').disable();
				statusEl.setStyle('color', "#336600");
				statusEl.update('Approved');
			}
			
			if (progress == 'No') {
				statusEl.setStyle('color', "#CC3333");	
				statusEl.update('Rejected');	
			}
			
			if (progress == 'Pending') {
				statusEl.setStyle('color', "#999999");
				statusEl.update('Awaiting Approval');
			}
			
			//show actual tmn data
			this.view.renderSummary(json, isOverseas, hasSpouse);
				
		},
		
		init: function() {
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');
			
			this.sessionStore = new Ext.data.JsonStore({
		        itemId:'session_store',
		        root: 'data',
		        fields:['SESSION_ID', 'SESSION_NAME', 'FIRSTNAME', 'SURNAME', 'EMAIL'],
		        url:'./php/auth/authviewer.php',
		        autoLoad: {
		        	params: { mode: 'load' }
		        }
		    });
			
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