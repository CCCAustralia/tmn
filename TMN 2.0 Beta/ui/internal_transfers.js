
Ext.ns('TMN');

TMN.Grid = new Ext.grid.EditorGridPanel({
	
	itemId: 'transfer_grid',
	height: 200,
	clicksToEdit: 1,
    selModel: new Ext.grid.RowSelectionModel({singleSelect:true}),
    
    ///////////////make sure these are set dynamically when GCX added
    guid: 'testuserguid',
    session: 2,
	
	set_guid_session: function (guid, session){
		this.guid = guid;
		this.session = session;
	},
	
    store: new Ext.data.JsonStore({
		itemId:'internal_transfers_store',
		root: 'transfers',
		fields:['TRANSFER_ID', 'SESSION_ID', 'TRANSFER_NAME', 'TRANSFER_AMOUNT'],
		url:'php/internal_transfers.php'
    }),
    
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
    
    tbar: [
    	{
    		text: 'Add',
    		iconCls: 'silk-add',
    		handler: function() {
    			var grid = TMN.Grid;
    			var Transfer = grid.getStore().recordType;
                var transfer = new Transfer({
                	SESSION_ID: grid.session,
                    TRANSFER_NAME: '',
                    TRANSFER_AMOUNT: 0
                });
                grid.stopEditing(true);
                index = grid.getStore().getCount();
                grid.getStore().insert(index, transfer);
                grid.getSelectionModel().selectLastRow(false);
                grid.startEditing(index, 0);
    		}
    	},
    	'-',
    	{
    		text: 'Remove',
    		iconCls: 'silk-delete',
    		handler: function() {
    			var grid = TMN.Grid;
    			
    			//make sure there is something to delete
    			if(grid.getSelectionModel().getCount() > 0){
    				grid.stopEditing(true);
    				grid.el.mask();
	    			var record = grid.getSelectionModel().getSelected();
	    			//if record is in the database remove it from both
	    			if (record.data.TRANSFER_ID !== undefined){
	    				//remove from DB
		    			Ext.Ajax.request({
		    				url: 'php/internal_transfers.php',
		    				params: {
		    					mode: 'remove',
		    					id: record.data.TRANSFER_ID,
		    					session: grid.session,
		    					name: record.data.TRANSFER_NAME,
		    					amount: record.data.TRANSFER_AMOUNT
		    				},
		    				success: function(grid){
		    					//remove from grid
		    					var record = grid.getSelectionModel().getSelected();
		    					grid.getStore().remove(record);
		                		grid.getSelectionModel().selectLastRow(false);
		                		grid.el.unmask();
		    				}.createDelegate(this, [grid])
		    			});
	    			} else {
	    				//if the record is only in the grid remove it from grid
	    				grid.getStore().remove(record);
	    				grid.el.unmask();
		                grid.getSelectionModel().selectLastRow(false);
	    			}
    			}
    		}
    	}
    ],
    
    listeners: {
    	afteredit: function(event){
    		record = event.record;
    		//if its not in the DB (has no ID) and doesn't have any default values in fields, add it to DB
    		if (record.data.TRANSFER_ID === undefined && record.data.TRANSFER_NAME != '' && record.data.TRANSFER_AMOUNT != 0){
    			//add record to DB
    			Ext.Ajax.request({
    				url: 'php/internal_transfers.php',
    				params: {
    					mode: 'add',
    					id: record.data.TRANSFER_ID,
    					session: event.grid.session,
    					name: record.data.TRANSFER_NAME,
    					amount: record.data.TRANSFER_AMOUNT
    				},
    				success: function(event){
    					//update grid with what is in DB
    					event.grid.getStore().load({params: {mode: 'get', session: event.grid.session}});
    				}.createDelegate(this, [event])
    			});
    		}
    		
    		//if record is in the DB (has an id) and has good values then update the row in the DB
    		if (record.data.TRANSFER_ID !== undefined && record.data.TRANSFER_NAME != '' && record.data.TRANSFER_AMOUNT != 0){
    			//update record in DB
    			Ext.Ajax.request({
    				url: 'php/internal_transfers.php',
    				params: {
    					mode: 'update',
    					id: record.data.TRANSFER_ID,
    					session: event.grid.session,
    					name: record.data.TRANSFER_NAME,
    					amount: record.data.TRANSFER_AMOUNT
    				},
    				success: function(event){
    					//update grid with what is in DB
    					event.grid.getStore().load({params: {mode: 'get', session: event.grid.session}});
    				}.createDelegate(this, [event])
    			});
    		}
    	}
    }
});