var ImageChooser = function(config) {
	// Setup a variable for the current directory
	var current_directory;
	
	/* ---- Begin side_navbar tree --- */
	this.tree = new Ext.tree.TreePanel({
		region: 'west',
		width: 150,
		minSize: 150,
		maxSize: 250,
		animate: true, 
		loader: new Ext.tree.TreeLoader({
			dataUrl: 'tree_data.json.php'
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
				this.store.load({
					params: {directory: node.attributes.url}
				});
			}
		}
	});
	
	// Add a tree sorter in folder mode
	new Ext.tree.TreeSorter(this.tree, {folderSort: true});
	/* ---- End side_navbar tree --- */
	
	/* ---- Begin dataview --- */
	this.initTemplates();
			
	this.store = new Ext.data.JsonStore({
		url: 'grid_data.json.php', //this.config.url,
		method: 'POST',
		baseParams: {'images_only': true},
		autoLoad: true,
		root: 'data',
		fields: [
			{name: 'name'},
			{name: 'size', type: 'float'},
			{name: 'type'},
			{name: 'relative_path'},
			{name: 'full_path'},
			{name: 'web_path'}
		],
		listeners: {
			scope: this,
			single: true,
			'load': function() {
				this.view.select(0);
			}
		}
	});
	
	this.lookup = {};
	var formatData = function(data){
		data.short_name = data.name.ellipse(15);
		this.lookup[data.name] = data;
		return data;
	};
	
	this.view = new Ext.DataView({
		tpl: this.thumbTemplate,
		singleSelect: true,
		overClass: 'x-view-over',
		itemSelector: 'div.thumb-wrap',
		emptyText : '<div style="padding:10px;">There are no images in this folder.</div>',
		store: this.store,
		listeners: {
			scope: this,
			'dblclick': this.doCallback,
			'beforeselect': function(view) {
				return view.store.getRange().length > 0;
			}
		},
		prepareData: formatData.createDelegate(this)
	});
	/* ---- End dataview --- */
	
	/* ---- Begin window --- */
	this.popup = new Ext.Window({
		id: 'ImageChooser',
		title: 'Choose An Image',
		width: config.width,
		height: config.height,
		minWidth: config.width,
		minHeight: config.height,
		layout: 'border',
		items: [
			this.tree,
			{
				region: 'center',
				autoScroll: true,
				items: this.view
			}
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

ImageChooser.prototype = {
	show: function(el, callback) {
		if (Ext.type(el) == 'object') {
			this.showEl = el.getEl();
		} else {
			this.showEl = el;
		}
		
		this.el = el;
		this.popup.show(this.showEl);
		this.callback = callback;
	},
	
	initTemplates: function(){
		this.thumbTemplate = new Ext.XTemplate(
			'<tpl for=".">',
				'<div class="thumb-wrap" id="{name}">',
				'<div class="thumb"><img src="{web_path}" title="{name}"></div>',
				'<span>{short_name}</span></div>',
			'</tpl>'
		);
		this.thumbTemplate.compile();
	},
	
	doCallback: function() {
		var selectedNode = this.view.getSelectedNodes()[0];
		var lookup = this.lookup;
		var callback = this.callback;
		var el = this.el;
		
		this.popup.hide(this.showEl, function() {
			if (selectedNode && callback) {
				var data = lookup[selectedNode.id].web_path;
				callback(el, data);
			}
		});
	}
};

String.prototype.ellipse = function(maxLength){
    if (this.length > maxLength){
        return this.substr(0, maxLength - 3) + '...';
    }
    return this;
};