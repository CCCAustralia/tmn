
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
	config = config || {itemId:'internal_transfers_panel', title:'Internal Transfers', load_url:'php/imp/internal_transfers.php', add_url:'php/imp/internal_transfers.php', remove_url:'php/imp/internal_transfers.php', update_url:'php/imp/internal_transfers.php'};
	
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
	this.load_url		=	config.load_url || 'php/imp/internal_transfers.php';
	/**
	 * @cfg {String}	add_url		The url of the server script that will process the grid's add request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.add_url		=	config.add_url || 'php/imp/internal_transfers.php';
	/**
	 * @cfg {String}	remove_url		The url of the server script that will process the grid's remove request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.remove_url		=	config.remove_url || 'php/imp/internal_transfers.php';
	/**
	 * @cfg {String}	update_url		The url of the server script that will process the grid's update request.<br />
	 * 									Default: 'php/internal_transfers.php'
	 */
	this.update_url		=	config.update_url || 'php/imp/internal_transfers.php';
	
	/**
	 * The TMN session that the user is working on. Is used by the backend to load, store and manipulate multiple sets of data for the user.
	 */
    this.session		= null;
    
    /**
	 * Marks the grid as saved or not. Please use isSaved() if you are testing for savedness
	 */
    this.saved			= true;
    
   this.setParams	= function(store, action, record, options, arg) {
	   //copy the reader's meta info across to the writer this needs to be done because of
	   //a bug in this version of ExtJS where they forget to do this.
	   this.Writer.meta = this.Reader.meta;
	   
	   //set params for request
	   options.params.session		= this.getSession();
	   
	   switch (action) {
	   		case 'create':
	   			options.params.mode	= 'add';
	   			break;
	   		case 'update':
	   			options.params.mode	= 'update';
	   			break;
	   		case 'destroy':
	   			options.params.mode	= 'remove';
	   			break;
	   		default:
	   			break;
	   }
	};
	
	this.Transfer		= Ext.data.Record.create([
	             		    {name: 'transfer_id',		type: 'int'},
	             		    {name: 'transfer_name',		type: 'string'},
	             		    {name: 'transfer_amount',	type: 'int'}
	             		]);
	
	 //the json writer to write json from the data store to a request that will go to the server
    this.Writer			= new Ext.data.JsonWriter({
							encode:			true,
							writeAllFields:	true
					    });
    
    //the json reader to read a response from the server, parse the json and put it in the store
    this.Reader			=  new Ext.data.JsonReader({
    						idProperty:	'transfer_id',
    						root:		'transfers',
    						fields:		this.Transfer
    					});
    
    //the proxy for the data store to handle the crud requests when needed
    this.Proxy			= new Ext.data.HttpProxy({
				        	api: {
					            read : {
					            	url:	this.load_url,
					            	method: 'POST'
					            },
					            create : {
					            	url:	this.add_url,
					            	method: 'POST'
					            },
					            update : {
					            	url:	this.update_url,
					            	method: 'POST'
					            },
					            destroy	: {
					            	url:	this.remove_url,
					            	method: 'POST'
					            }
				        	}
    					});
	
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
			itemId:			'internal_transfers_store',
			autoDestroy:	true,
			proxy:			this.Proxy,
			reader:			this.Reader,
			writer:			this.Writer,
			autoSave:		false,
			batch:			true,
        	listeners: {
        		//before requests are sent change the params so that the session at that time is sent in the request
        		beforewrite: 	this.setParams,
        		save:			function() {
        			this.getView().refresh();
        		},
        		load:			function() {
        			this.getView().refresh();//TODO: get grid to show returned data
        		},
        		scope: 			this
        	}
	    }),
	    
	    //defines the columns of the grid
	    colModel: new Ext.grid.ColumnModel({
	        defaults: {
	            width: 120
	        },
	        columns: [
	            {
	            	header:		'Transfer Name', 
	            	dataIndex:	'transfer_name',
	            	editor:		new Ext.form.TextField({
	            		allowBlank: false
	            	})
	            },
	            {
	            	header:		'Transfer Amount',
	            	dataIndex:	'transfer_amount',
	            	editor:		new Ext.form.NumberField({
	            		allowBlank: false,
	            		minValue: 0
	            	})
	            }
	        ]
	    }),
	    
	    //defines the toolbar with its buttons and their actions
	    tbar: [
	    	{
	    		itemId:		'add',
	    		text:		'Add',
	    		iconCls:	'silk-add',
	    		scope:		this,
	    		handler:	function(button, event) {
		    		//mark the grid as unsaved
		    		this.saved = false;
		    		
	    			var grid = this;
	                var transfer = new this.Transfer({			//create a new one with
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
	    		itemId:		'remove',
	    		text:		'Remove',
	    		iconCls:	'silk-delete',
	    		scope:		this,
	    		handler:	function() {
	    			
		    		//mark the grid as unsaved
		    		this.saved = false;
	    			
	    			var grid = this;
	    			//make sure there is something to delete
	    			if(grid.getSelectionModel().getCount() > 0){
	    				
	    				//stop what the user is doing
	    				grid.stopEditing(true);
	    				
	    				//remove the record from the grid
		    			var record = grid.getSelectionModel().getSelected();
	    				grid.getStore().remove(record);
		                grid.getSelectionModel().selectLastRow(false);
		                
		                //let the user add and delete again after taks completed
						this.getTopToolbar().items.map['add'].enable();
						this.getTopToolbar().items.map['remove'].enable();
	    			}
	    		}
	    	}
	    ],
	    
	    listeners: {
			scope:		this,
	    	afteredit:	function(event){
	    		
	    		//mark the grid as unsaved
	    		this.saved = false;
	    		
	    		//make sure the sure has finished editing the record they have before letting them have another one
	    		var editedRecord = event.record;
	    		if (editedRecord.data.transfer_name != '' && editedRecord.data.transfer_amount != 0) {
	    			this.getTopToolbar().items.map['add'].enable();
					this.getTopToolbar().items.map['remove'].enable();
	    		}
	    		
	    		//make sure no transfer has the same name as another
	    		//grab all the records in the store
	    		var recordArray		= this.getStore().getRange();
	    		//if it's a single record put it in an array
	    		if (recordArray.data !== undefined) {
	    			recordArray = [recordArray];
	    		}
	    		console.log(recordArray);
	    		//loop through all the records adding their json to the transfer array
	    		for (recordCount = 0; recordCount < recordArray.length; recordCount++) {
	    			record	= recordArray[recordCount];
	    			console.log(record);
	    			if (record != 0 && record !== undefined) {
		    			if (editedRecord.id != record.id && editedRecord.data.transfer_name == record.data.transfer_name) {
		    				//add the amount just entered to the existing record
		    				if (editedRecord.data.transfer_amount > 0) {
		    					record.set('transfer_amount', record.data.transfer_amount + editedRecord.data.transfer_amount);
		    				}
		    				//delete the record that it being edited
		    				this.getStore().remove(editedRecord);
		    				//tell the user not to use the same name for a transfer
		    				Ext.Msg.show({
		    					icon: Ext.MessageBox.WARNING,
		    					buttons: Ext.MessageBox.OK,
		    					closable: false,
		    					title: 'Naming Error',
		    					msg: "You can't have two transfers with the same name. The amount from the transfer you just created has been added to the existing transfer with the same name."
		    				});
		    				
		    				break;
		    			}
	    			}
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
	 * Tells you if changes have been saved or not
	 */
	isSaved: function() {
		return this.saved;
	},
	
	/**
	 * Gets the id of the current session that is being modified.
	 * @return {number}	session		The number representing the user's session.
	 */
	getSession: function (){
		return this.session;
	},
	
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
		
		//set the grid's session to the one passed in load
		this.setSession(session);
		
		this.getStore().load({
			params:		{mode: 'get', session: this.getSession()},
			add:		false,
			scope:		this,
			callback:	function() {
				this.getTopToolbar().items.map['add'].enable();
				this.getTopToolbar().items.map['remove'].enable();
			}
		});
		
		//mark the grid as saved
		this.saved = true;
	},
	
	/**
	 * Will save any changes made since the last save.
	 */
	saveInternalTransfers: function() {
		
		//save all the changes in the store
		this.getStore().save();
		
		//mark the grid as saved
		this.saved = true;
	},
	
	/**
	 * Will save everything in the store under a new session.
	 */
	saveAsInternalTransfers: function(session) {
		//set the session
		this.setSession(session);
		
		//loop through all records creating an ajax request to add them all
		var transferArray	=	this.getTransferArray();
		
		Ext.Ajax.request({
			url: this.update_url,
			scope: this,
			params: {
				mode:		'add',
				session:	this.getSession(),
				transfers:	Ext.encode(transferArray)
			},
			success: function(event){
				//update grid with what is in DB
				this.loadInternalTransfers(this.getSession());
				//mark the grid as saved
				this.saved = true;
			}
		});
	},
	
	deleteInternalTransfers: function() {
		
		//create an ajax request to delete them all
		
		Ext.Ajax.request({
			url: this.update_url,
			scope: this,
			params: {
				mode:		'deleteall',
				session:	this.getSession()
			},
			success: function(event){
				//update grid with what is in DB
				this.resetInternalTransfers();
			}
		});
		
	},
	
	getTransferArray: function() {
		
		var transferArray	= [];
		//grab all the records in the store
		var recordArray		= this.getStore().getRange();
		
		//loop through all the records adding their json to the transfer array
		for (recordCount = 0; recordCount < recordArray.length; recordCount++) {
			transferArray[recordCount]	= recordArray[recordCount].json;
			recordCount++;
		}
		
		return transferArray;
	},
	
	resetInternalTransfers: function() {
		
		this.setSession(null);
		
		//clear the data from this grid
		this.getStore().removeAll();

		//mark the grid as saved
		this.saved = true;
	}
});