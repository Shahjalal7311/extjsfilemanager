<?
// Start the session for this page
session_start();
header("Cache-control: private");

// Include main config file
include ("includes/config.inc.php");

// Include common functions
include ("includes/functions.inc.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>File Manager</title>
<link rel="stylesheet" type="text/css" href="/scripts/ext-2.0/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="/scripts/ext-2.0/resources/css/xtheme-gray.css" />
<link rel="stylesheet" type="text/css" href="css/styles.css" />

<script language="javascript" type="text/javascript" src="/scripts/ext-2.0/adapter/ext/ext-base.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/ext-2.0/ext-all.js"></script>

<link rel="stylesheet" type="text/css" href="includes/Ext.ux.UploadDialog/css/Ext.ux.UploadDialog.css" />
<script language="javascript" type="text/javascript" src="includes/Ext.ux.UploadDialog/Ext.ux.UploadDialog.packed.js"></script>
<script language="javascript" type="text/javascript" src="includes/Ext.ux.ImageEditor/Ext.ux.ImageEditor.js"></script>

<script language="javascript" type="text/javascript">
	Ext.onReady(function(){
		Ext.QuickTips.init();
		Ext.form.Field.prototype.msgTarget = 'side';
		
		// Remove the loading panel after the page is loaded
		Ext.get('loading').remove();
		Ext.get('loading_mask').fadeOut({remove:true});
		
		// Setup a variable for the current directory
		var current_directory = '';
		
		/* ---- Begin side_navbar tree --- */
		var tree = new Ext.tree.TreePanel({
			autoScroll: true,
			animate: true,
			containerScroll: true,
			border: false,
			enableDD: true,
			ddGroup : 'fileMove',
			loader: new Ext.tree.TreeLoader({
				dataUrl: 'tree_data.json.php'
			}),
			root: new Ext.tree.AsyncTreeNode({
				text: 'Files',
				draggable: false,
				id: 'source',
				expanded: true
			}),
			listeners: {
				'click': function(node, e) {
					current_directory = node.attributes.url;
					ds.load({
						params: {directory: node.attributes.url},
						callback: do_buttons
					});
				},
				'contextmenu': function(node, e) {
					node.select();
					context_menu.node = node;
					context_menu.show(e.getTarget());
				},
				'beforenodedrop': do_move
			}
		});
		
		// Add a tree sorter in folder mode
		new Ext.tree.TreeSorter(tree, {folderSort: true});
		/* ---- End side_navbar tree --- */
		
		/* ---- Begin side_navbar context menu --- */
		var context_menu = new Ext.menu.Menu({
			id: 'context_menu',
			items: [{
				text: 'New Directory',
				iconCls: 'new_directory_button',
				handler: do_new_directory
			},{
				text: 'Rename Directory',
				iconCls: 'rename_directory_button',
				handler: do_rename_directory
			},{
				text: 'Chmod Directory',
				iconCls: 'chmod_directory_button',
				handler: do_chmod_directory
			},{
				text: 'Delete Directory',
				iconCls: 'delete_directory_button',
				handler: do_delete_directory
			}]
		});
		/* ---- End side_navbar context menu --- */
		
		/* ---- Begin grid --- */
		var ds = new Ext.data.GroupingStore({
			url: 'grid_data.json.php',
			method: 'POST',
			autoLoad: true,
			sortInfo: {field: 'name', direction: 'ASC'},
			reader: new Ext.data.JsonReader({
				root: 'data',
				totalProperty: 'count'
			},[
				{name: 'name'},
				{name: 'size'},
				{name: 'type'},
				{name: 'permissions'},
				{name: 'ctime', type: 'date', dateFormat: 'timestamp'},
				{name: 'mtime', type: 'date', dateFormat: 'timestamp'},
				{name: 'owner'},
				{name: 'group'},
				{name: 'relative_path'},
				{name: 'full_path'},
				{name: 'web_path'}
			])
		});
		
		var cm = new Ext.grid.ColumnModel([
			{header: 'Name', dataIndex: 'name', sortable: true},
			{header: 'Size', dataIndex: 'size', sortable: true, renderer: Ext.util.Format.fileSize},
			{header: 'Type', dataIndex: 'type', sortable: true},
			{header: 'Permissions', dataIndex: 'permissions', sortable: true},
			{header: 'Created', dataIndex: 'ctime', sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
			{header: 'Modified', dataIndex: 'mtime', sortable: true, renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
			{header: 'Owner', dataIndex: 'owner', sortable: true},
			{header: 'Group', dataIndex: 'group', sortable: true},
			{header: 'Relative Path', dataIndex: 'relative_path', sortable: true, hidden: true},
			{header: 'Full Path', dataIndex: 'full_path', sortable: true, hidden: true},
			{header: 'Web Path', dataIndex: 'web_path', sortable: true, hidden: true}
		]);
		
		var grid = new Ext.grid.GridPanel({
			anchor: '0 100%',
			border: false,
			enableDrag: true,
			ddGroup : 'fileMove',
			view: new Ext.grid.GroupingView({
				emptyText: 'This folder contains no files.',
				forceFit: true,
				showGroupName: false,
				enableNoGroups: true
			}),
			ds: ds,
			cm: cm,
			listeners: {
				'rowClick': function () {
					do_buttons();
				}
			}
		});
		/* ---- End grid --- */
		
		/* --- Begin Main Layout --- */
		var viewport = new Ext.Viewport({
			layout: 'border',
			items: [{
				region: 'west',
				border: false,
				split: true,
				collapseMode: 'mini',
				width: 200,
				items: tree
			},{
				region: 'center',
				layout: 'anchor',
				border: false,
				tbar: new Ext.StatusBar({
					id: 'status_bar',
					defaultText: '',
					defaultIconCls: '',
					statusAlign: 'right',
					items: [{
						id: 'upload_button',
						text: 'Upload',
						tooltip: 'Upload New File',
						iconCls: 'upload_button',
						handler: do_upload
					},{
						id: 'download_button',
						text: 'Download',
						tooltip: 'Download Selected File',
						iconCls: 'download_button',
						disabled: true,
						handler: do_download
					},{
						id: 'rename_button',
						text: 'Rename',
						tooltip: 'Rename Selected File',
						iconCls: 'rename_button',
						disabled: true,
						handler: do_rename
					},{
						id: 'chmod_button',
						text: 'Chmod',
						tooltip: 'Chmod Selected File',
						iconCls: 'chmod_button',
						disabled: true,
						handler: do_chmod
					},{
						id: 'delete_button',
						text: 'Delete',
						tooltip: 'Delete Selected File',
						iconCls: 'delete_button',
						disabled: true,
						handler: do_delete
					},'-',{
						id: 'edit_image_button',
						text: 'Edit Image',
						tooltip: 'Edit Selected Image',
						iconCls: 'edit_image_button',
						disabled: true,
						handler: do_edit_image
					}]
				}),
				items: grid
			}]
		});
		/* --- End Main Layout --- */
		
		/* --- Begin Functions --- */
		function do_buttons() {
			var row = grid.getSelectionModel().getSelected();
			
			if (row != null) {
				Ext.getCmp('download_button').enable();
				Ext.getCmp('rename_button').enable();
				Ext.getCmp('chmod_button').enable();
				Ext.getCmp('delete_button').enable();
				if (row.data.name.match(/\.(jpeg|jpg|gif|png)$/)) {
					Ext.getCmp('edit_image_button').enable();
				} else {
					Ext.getCmp('edit_image_button').disable();
				}
			} else {
				Ext.getCmp('download_button').disable();
				Ext.getCmp('rename_button').disable();
				Ext.getCmp('chmod_button').disable();
				Ext.getCmp('edit_image_button').disable();
			}
		}
		
		function do_upload() {
			var upload_dialog = new Ext.ux.UploadDialog.Dialog({
				title: 'Upload Files',
				url: 'actions.php',
				base_params: {action: 'upload', directory: current_directory},
				minWidth: 400,
				minHeight: 200,
				width: 400,
				height: 350,
				reset_on_hide: false,
				allow_close_on_upload: false
			});
			upload_dialog.show('upload_button');
			upload_dialog.on("uploadcomplete", function() {
				ds.reload();
			});
			upload_dialog.on("hide", function() {
				this.destroy(true);
			});
		}
		
		function do_download() {
			var row = grid.getSelectionModel().getSelected();
			self.location = 'actions.php?action=download&directory=' + current_directory + '&file=' + row.data.name;
		}
		
		function do_rename() {
			var row = grid.getSelectionModel().getSelected();
			
			var rename_form = new Ext.FormPanel({
				url: 'actions.php',
				method: 'POST',
				bodyStyle: 'padding:10px',
				border: false,
				items: [
					new Ext.form.TextField({
						fieldLabel: 'Name',
						name: 'new_name',
						value: row.data.name,
						width: 'auto'
					})
				]
			});
			
			var rename_window = new Ext.Window({
				title: 'Rename File',
				width: 340,
				closable: true,
				resizable: false,
				buttons: [{
					text: 'Save',
					handler: function() {
						rename_form.getForm().submit({
							waitMsg: 'Processing Data, please wait...',
							params: {action: 'rename', directory: current_directory, file: row.data.name},
							success: function() {
								ds.reload({
									callback: do_buttons
								});
								rename_window.hide();
							},
							failure: function() {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: 'Error: Could not rename file',
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						});
					}
				},{
					text: 'Cancel',
					handler: function() {
						rename_window.hide();
					}
				}],
				items: rename_form
			});
			
			rename_window.show('rename_button');
		}
		
		function do_chmod() {
			var row = grid.getSelectionModel().getSelected();
			
			var chmod_form = new Ext.FormPanel({
				url: 'actions.php',
				method: 'POST',
				bodyStyle: 'padding:10px',
				border: false,
				items: [{
					layout:'column',
					border: false,
					items: [{
						columnWidth:.33,
						xtype: 'fieldset',
						title: 'Owner',
						bodyStyle: 'padding: 5px;',
						autoHeight: true,
						items: [{
							xtype: 'checkbox',
							name: 'owner_read',
							boxLabel: 'Read',
							width: 'auto',
							checked: (row.data.permissions.substr(1, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'owner_write',
							boxLabel: 'Write',
							width: 'auto',
							checked: (row.data.permissions.substr(2, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'owner_execute',
							boxLabel: 'Execute',
							width: 'auto',
							checked: (row.data.permissions.substr(3, 1) != "-" ? true : false),
							hideLabel: true
						}]
					},{
						columnWidth:.33,
						xtype: 'fieldset',
						title: 'Group',
						bodyStyle: 'padding: 5px;',
						autoHeight: true,
						items: [{
							xtype: 'checkbox',
							name: 'group_read',
							boxLabel: 'Read',
							width: 'auto',
							checked: (row.data.permissions.substr(4, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'group_write',
							boxLabel: 'Write',
							width: 'auto',
							checked: (row.data.permissions.substr(5, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'group_execute',
							boxLabel: 'Execute',
							width: 'auto',
							checked: (row.data.permissions.substr(6, 1) != "-" ? true : false),
							hideLabel: true
						}]
					},{
						columnWidth:.33,
						xtype: 'fieldset',
						title: 'Everyone',
						bodyStyle: 'padding: 5px;',
						autoHeight: true,
						items: [{
							xtype: 'checkbox',
							name: 'everyone_read',
							boxLabel: 'Read',
							width: 'auto',
							checked: (row.data.permissions.substr(7, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'everyone_write',
							boxLabel: 'Write',
							width: 'auto',
							checked: (row.data.permissions.substr(8, 1) != "-" ? true : false),
							hideLabel: true
						},{
							xtype: 'checkbox',
							name: 'everyone_execute',
							boxLabel: 'Execute',
							width: 'auto',
							checked: (row.data.permissions.substr(9, 1) != "-" ? true : false),
							hideLabel: true
						}]
					}]
				}]
			});
			
			var chmod_window = new Ext.Window({
				title: 'Chmod File',
				width: 340,
				closable: true,
				resizable: false,
				buttons: [{
					text: 'Save',
					handler: function() {
						chmod_form.getForm().submit({
							waitMsg: 'Processing Data, please wait...',
							params: {action: "chmod", directory: current_directory, file: row.data.name},
							success: function() {
								ds.reload({
									callback: do_buttons
								});
								chmod_window.hide();
							},
							failure: function() {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: 'Error: Could not chmod file',
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						});
					}
				},{
					text: 'Cancel',
					handler: function() {
						chmod_window.hide();
					}
				}],
				items: chmod_form
			});
			
			chmod_window.show('chmod_button');
		}
		
		function do_delete() {
			Ext.MessageBox.confirm('Confirm', 'Are you sure you want to delete this file?', function(reponse) {
				if (reponse == "yes") {
					var row = grid.getSelectionModel().getSelected();
					
					var connection = new Ext.data.Connection().request({
						url: "actions.php",
						method: "POST",
						params: {action: "delete", directory: current_directory, file: row.data.name},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success == true) {
								ds.reload();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						},
						failure: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					});
				}
			});
		}
		
		function do_edit_image() {
			var row = grid.getSelectionModel().getSelected();
			
			var edit_image_window = new Ext.ux.ImageEditor({
				image: row.data.relative_path
			});
			
			edit_image_window.show('edit_image_button');
		}
		
		function do_move(o) {
			for(i = 0; i < o.data.selections.length; i++){
				var row = o.data.selections[i];
				
				var connection = new Ext.data.Connection().request({
					url: "actions.php",
					method: "POST",
					params: {'action': 'move', 'directory': current_directory, 'file': row.data.name, 'new_directory': (o.target.attributes.url ? o.target.attributes.url : '')},
					success: function(o) {
						var response = Ext.util.JSON.decode(o.responseText);
						
						if (response.success == true) {
							ds.reload();
						} else {
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					},
					failure: function(o) {
						var response = Ext.util.JSON.decode(o.responseText);
						// Set a status bar message
						Ext.getCmp('status_bar').setStatus({
							text: response.message,
							iconCls: 'save_warning_icon',
							clear: true
						});
					}
				});
			}
		}
		
		function do_new_directory() {
			Ext.MessageBox.prompt('New Directory', 'New Directory Name', function(reponse, text) {
				if (reponse == "ok") {
					var connection = new Ext.data.Connection().request({
						url: "actions.php",
						method: "POST",
						params: {action: "new_directory", directory: context_menu.node.attributes.url, new_directory: text},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success == true) {
								tree.getRootNode().reload();
								tree.getRootNode().expand();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						},
						failure: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					});
				}
			});
		}
		
		function do_rename_directory() {
			Ext.MessageBox.prompt('Rename Directory', 'New Directory Name', function(reponse, text) {
				if (reponse == "ok") {
					var connection = new Ext.data.Connection().request({
						url: "actions.php",
						method: "POST",
						params: {action: "rename_directory", directory: context_menu.node.attributes.url, new_name: text},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success == true) {
								tree.getRootNode().reload();
								tree.getRootNode().expand();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						},
						failure: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					});
				}
			});
		}
		
		function do_chmod_directory() {
			Ext.MessageBox.prompt('Chmod Directory', 'Permissions', function(reponse, text) {
				if (reponse == "ok") {
					var connection = new Ext.data.Connection().request({
						url: "actions.php",
						method: "POST",
						params: {action: "chmod_directory", directory: context_menu.node.attributes.url, permissions: text},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success == false) {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						},
						failure: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					});
				}
			});
		}
		
		function do_delete_directory() {
			Ext.MessageBox.confirm('Confirm', 'Are you sure you want to delete this directory?', function(reponse) {
				if (reponse == "yes") {
					var connection = new Ext.data.Connection().request({
						url: "actions.php",
						method: "POST",
						params: {action: "delete_directory", directory: context_menu.node.attributes.url},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success == true) {
								tree.getRootNode().reload();
								tree.getRootNode().expand();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						},
						failure: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							// Set a status bar message
							Ext.getCmp('status_bar').setStatus({
								text: response.message,
								iconCls: 'save_warning_icon',
								clear: true
							});
						}
					});
				}
			});
		}
		/* --- End Functions --- */
	});
</script>
</head>

<body>
	<div id="loading_mask"></div>
	<div id="loading"> 
		<div id="loading_indicator"><img src="images/loading_indicator.gif" alt="Loading" /> Loading...</div>
	</div>
</body>
</html>