var FileChooser = function(config) {
	// Setup a variable for the current directory
	this.current_directory;
	
	/* ---- Begin side_navbar tree --- */
	this.tree = new Ext.tree.TreePanel({
		region: 'west',
		width: 150,
		minSize: 150,
		maxSize: 250,
		animate: true, 
		loader: new Ext.tree.TreeLoader({
			dataUrl: 'tree-data.json.php'
		}),
		enableDD: true,
		containerScroll: true,
		rootVisible:true,
		root: new Ext.tree.AsyncTreeNode({
			text: 'Files',
			draggable: false,
			id: 'source',
			expanded: true
		}),
		listeners: {
			scope: this,
			'click': function(node, e) {
				current_directory = node.attributes.url;
				this.ds.load({
					params: {directory: node.attributes.url}
				});
			}
		}
	});
	
	// Add a tree sorter in folder mode
	new Ext.tree.TreeSorter(this.tree, {folderSort: true});
	/* ---- End side_navbar tree --- */
	
	/* ---- Begin grid --- */
	this.ds = new Ext.data.GroupingStore({
		url: 'actions.php',
		method: 'POST',
		autoLoad: true,
		sortInfo: {field: 'name', direction: 'ASC'},
		reader: new Ext.data.JsonReader({
			root: 'data',
			totalProperty: 'count'
		},[
			{name: 'name'},
			{name: 'size', type: 'float'},
			{name: 'type'},
			{name: 'relative_path'},
			{name: 'full_path'},
			{name: 'web_path'}
		])
	});
	
	this.cm = new Ext.grid.ColumnModel([
		{header: 'Name', dataIndex: 'name', sortable: true},
		{header: 'Size', dataIndex: 'size', sortable: true, renderer: Ext.util.Format.fileSize},
		{header: 'Type', dataIndex: 'type', sortable: true},
		{header: 'Relative Path', dataIndex: 'relative_path', sortable: true, hidden: true},
		{header: 'Full Path', dataIndex: 'full_path', sortable: true, hidden: true},
		{header: 'Web Path', dataIndex: 'web_path', sortable: true, hidden: true}
	]);
	
	this.grid = new Ext.grid.GridPanel({
		region: 'center',
		border: false,
		view: new Ext.grid.GroupingView({
			emptyText: 'This folder contains no files.',
			forceFit: true,
			showGroupName: false,
			enableNoGroups: true
		}),
		ds: this.ds,
		cm: this.cm,
		listeners: {
			scope: this,
			'rowdblclick': this.doCallback
		}
	});
	/* ---- End grid --- */
	
	/* ---- Begin window --- */
	this.popup = new Ext.Window({
		id: 'FileChooser',
		title: 'Choose A File',
		width: config.width,
		height: config.height,
		minWidth: config.width,
		minHeight: config.height,
		layout: 'border',
		items: [
			this.tree,
			this.grid
		],
		buttons: [{
			text: 'Ok',
			scope: this,
			handler: this.doCallback
		},{
			text: 'Cancel',
			scope: this,
			handler: function() {
				this.popup.hide();
			}
		}]
	});
	/* ---- End window --- */
};

FileChooser.prototype = {
	show : function(el, callback) {
		if (Ext.type(el) == 'object') {
			this.showEl = el.getEl();
		} else {
			this.showEl = el;
		}
		
		this.el = el;
		this.popup.show(this.showEl);
		this.callback = callback;
	},
	
	doCallback : function() {
		var row = this.grid.getSelectionModel().getSelected();
		var callback = this.callback;
		var el = this.el;
		
		this.popup.hide(this.showEl, function() {
			if (row && callback) {
				var data = row.data.web_path;
				callback(el, data);
			}
		});
	}
};