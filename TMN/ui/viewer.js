Ext.ns('tmn');

tmn.viewer = function() {

	return {
		
		display: function(panel){
			var data		= G_DATA,
				isOverseas	= G_ISOVERSEAS,
				hasSpouse	= G_HASSPOUSE;
			
			panel.renderSummary(data, isOverseas, hasSpouse);
		},
		
		init: function() {
			var loadingMask = Ext.get('loading-mask');
			var loading = Ext.get('loading');
			
			//create view
			this.view = new tmn.view.SummaryPanel;
			this.view.on('afterrender', this.display, this);
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