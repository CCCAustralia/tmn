
Ext.ns('tmn', 'tmn.view');

/**
 * @class		tmn.view.InternalTransfers
 * 
 * <p>
 * <b>Description:</b> The Grid that manages the user's internal transfers.<br />
 * It adds, removes, updates and loads internal transfers from the database via a php backend.
 * </p>
 * 
 * @author		Michael Harrison	(<a href="mailto:michael.harrison@ccca.org.au">michael.harrison@ccca.org.au</a>)
 * 				& Thomas Flynn		(<a href="mailto:tom.flynn@ccca.org.au">tom.flynn@ccca.org.au</a>)
 * 
 * @namespace 	tmn.view
 * @extends		Ext.grid.EditorGridPanel
 * @version		TMN 2.1.0
 * @note		The TMN uses the MVC design structure, read up on it at <a href="http://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller">http://en.wikipedia.org/wiki/Model-view-controller</a>).
 * @demo		http://mportal.ccca.org.au/TMN
 */
tmn.view.InternalTransfers = function(config) {
	//set config variable to passed or default
	config = config || {itemId:'internal_transfers_panel', title:'Internal Transfers', load_url:'php/internal_transfers.php', add_url:'php/internal_transfers.php', remove_url:'php/internal_transfers.php', update_url:'php/internal_transfers.php'};
	
	//set config options to passed or default
	/**
	 * @cfg {String}	itemId			The local id of this panel. It will only have this name in the context of the object that contains it. Its html id will be auto genertated.<br />
	 * 									Default: 'internal_transfers_panel'
	 */
	this.itemId			=	config.itemId || 'internal_transfers_panel';
	/**
	 * @cfg {String}	title			The title displayed in the header of the form.<br />
	 * 									Default: 'Internal Transfers'
	 */
	this.title			=	config.title || 'Internal Transfers';
	/**
	 * @cfg {String}	load_url		The url of the server script that will process the grid's load request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.load_url		=	config.load_url || 'php/internal_transfers.php';
	/**
	 * @cfg {String}	add_url		The url of the server script that will process the grid's add request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.add_url		=	config.add_url || 'php/internal_transfers.php';
	/**
	 * @cfg {String}	remove_url		The url of the server script that will process the grid's remove request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.remove_url		=	config.remove_url || 'php/internal_transfers.php';
	/**
	 * @cfg {String}	update_url		The url of the server script that will process the grid's update request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.update_url		=	config.update_url || 'php/internal_transfers.php';
	
	/**
	 * The TMN session that the user is working on. Is used by the backend to load, store and manipulate multiple sets of data for the user.
	 */
    this.session = 2;
	
    /**
	 * The config that defines the physical layout of the panel.
	 * It is only used in construtor so there is no use in changing it dynamically. Edit it in the source.
	 */
	this.config = {
			//config options
		itemId: this.itemId,
		title: this.title,
		height: 200,
		clicksToEdit: 1,
	    selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
	    
	    //the store for the data to go in
	    store: new Ext.data.JsonStore({
			itemId:'internal_transfers_store',
			root: 'transfers',
			fields:['TRANSFER_ID', 'SESSION_ID', 'TRANSFER_NAME', 'TRANSFER_AMOUNT'],
			url: this.load_url
	    }),
	    
	    //defines the columns of the grid
	    colModel: new Ext.grid.ColumnModel({
	        defaults: {
	            width: 120
	        },
	        columns: [
	            {
	            	header: 'Transfer Name', 
	            	dataIndex: 'TRANSFER_NAME',
	            	editor: new Ext.form.TextField({
	            		allowBlank: false
	            	})
	            },
	            {
	            	header: 'Transfer Amount',
	            	dataIndex: 'TRANSFER_AMOUNT',
	            	editor: new Ext.form.NumberField({
	            		allowBlank: false,
	            		minValue: 0
	            	})
	            }
	        ]
	    }),
	    
	    //defines the toolbar with its buttons and their actions
	    tbar: [
	    	{
	    		itemId: 'add',
	    		text: 'Add',
	    		iconCls: 'silk-add',
	    		scope: this,
	    		handler: function(button, event) {
	    			var grid = this;
	    			var Transfer = grid.getStore().recordType;	//grab the type of the stored data
	                var transfer = new Transfer({				//create a new one with
	                	SESSION_ID: grid.session,				//the session
	                    TRANSFER_NAME: '',						//a blank name
	                    TRANSFER_AMOUNT: 0						//and zero dollars
	                });
	                grid.stopEditing(true);						//stop what is happening
	                index = grid.getStore().getCount();			//grab the index of the new record (to put after the last one)
	                grid.getStore().insert(index, transfer);	//add it
	                grid.getSelectionModel().selectLastRow(false);	//select it
	                grid.startEditing(index, 0);				//get the user to edit it
	                button.disable();							//disable the add button so that no more can be added till this one is finished
	    		}
	    	},
	    	'-',
	    	{
	    		itemId: 'remove',
	    		text: 'Remove',
	    		iconCls: 'silk-delete',
	    		scope: this,
	    		handler: function() {
	    			var grid = this;
	    			
	    			//make sure there is something to delete
	    			if(grid.getSelectionModel().getCount() > 0){
	    				grid.stopEditing(true);		//stop what the user is doing
	    				grid.el.mask();				//block the user out of the grid till the remove is complete
		    			var record = grid.getSelectionModel().getSelected();
		    			//if record is in the database remove it from both
		    			if (record.data.TRANSFER_ID !== undefined){
		    				//remove from DB
			    			Ext.Ajax.request({
			    				url: this.remove_url,
			    				scope: this,
			    				params: {
			    					mode: 'remove',
			    					id: record.data.TRANSFER_ID,
			    					session: grid.session,
			    					name: record.data.TRANSFER_NAME,
			    					amount: record.data.TRANSFER_AMOUNT
			    				},
			    				success: function(grid){
			    					//remove from grid
			    					var record = this.getSelectionModel().getSelected();
			    					this.getStore().remove(record);
			    					this.getSelectionModel().selectLastRow(false);
			    					this.el.unmask();								//when its complete let the user use the grid again
			    					this.getTopToolbar().items.map['add'].enable();
			    					this.getTopToolbar().items.map['remove'].enable();
			    				}
			    			});
		    			} else {
		    				//if the record is only in the grid remove it from grid
		    				grid.getStore().remove(record);
		    				grid.el.unmask();										//when its complete let the user use the grid again
			                grid.getSelectionModel().selectLastRow(false);
							this.getTopToolbar().items.map['add'].enable();
							this.getTopToolbar().items.map['remove'].enable();
		    			}
	    			}
	    		}
	    	}
	    ],
	    
	    listeners: {
			scope: this,
	    	afteredit: function(event){
	    		
	    		record = event.record;
	    		//if its not in the DB (has no ID) and doesn't have any default values in fields, add it to DB
	    		if (record.data.TRANSFER_ID === undefined && record.data.TRANSFER_NAME != '' && record.data.TRANSFER_AMOUNT != 0){
	    			//add record to DB
					this.getTopToolbar().items.map['add'].disable();
					this.getTopToolbar().items.map['remove'].disable();
	    			Ext.Ajax.request({
	    				url: this.add_url,
	    				scope: this,
	    				params: {
	    					mode: 'add',
	    					id: record.data.TRANSFER_ID,
	    					session: event.grid.session,
	    					name: record.data.TRANSFER_NAME,
	    					amount: record.data.TRANSFER_AMOUNT
	    				},
	    				success: function(event){
	    					//update grid with what is in DB
	    					this.loadInternalTransfers(this.session);
	    				}
	    			});
	    		}
	    		
	    		//if record is in the DB (has an id) and has good values then update the row in the DB
	    		if (record.data.TRANSFER_ID !== undefined && record.data.TRANSFER_NAME != '' && record.data.TRANSFER_AMOUNT != 0){
	    			//update record in DB
					this.getTopToolbar().items.map['add'].disable();
					this.getTopToolbar().items.map['remove'].disable();
	    			Ext.Ajax.request({
	    				url: this.update_url,
	    				scope: this,
	    				params: {
	    					mode: 'update',
	    					id: record.data.TRANSFER_ID,
	    					session: event.grid.session,
	    					name: record.data.TRANSFER_NAME,
	    					amount: record.data.TRANSFER_AMOUNT
	    				},
	    				success: function(event){
	    					//update grid with what is in DB
	    					this.loadInternalTransfers(this.session);
	    				}
	    			});
	    		}
	    	}
	    }
	};
	
	//this is a call to tmn.view.InternalTransfers's parent constructor (Ext.grid.EditorGridPanel), this will give tmn.view.InternalTransfers all the variables and methods that it's parent does
	tmn.view.InternalTransfers.superclass.constructor.call(this, this.config);
};
	
//This is the section of the file where the methods are defined. The structure of the visuals is defined above this, in the construtor.
Ext.extend(tmn.view.InternalTransfers, Ext.grid.EditorGridPanel, {
	
	/**
	 * Sets the id of the current session that is being modified.
	 * @param {number}	session		The number representing the user's session.
	 */
	setSession: function (session){
		this.session = session;
	},
	
	/**
	 * Will load the form with an ajax request to php/internal_transfers.php.
	 * It will enable the use of the add remove buttons once its finished loading.
	 */
	loadInternalTransfers: function(session) {
		this.getStore().load({
			url: this.load_url,
			params: {mode: 'get', session: session},
			scope: this,
			callback: function() {
				this.getTopToolbar().items.map['add'].enable();
				this.getTopToolbar().items.map['remove'].enable();
			}
		});
	}
});