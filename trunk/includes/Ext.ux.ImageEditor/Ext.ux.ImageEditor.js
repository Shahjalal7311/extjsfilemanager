Ext.namespace('Ext.ux');

Ext.ux.ImageEditor = Ext.extend(Ext.Window, {
	title: 'Edit Image',
	width: 500,
	height: 400,
	modal: true,
	closable: true,
	resizable: true,
	maximizable: true,
	image: '',
	imageWidth: '',
	imageHeight: '',
	imageProportions: '',
	
	initComponent: function() {
		// Send a request to create a temp image so we can edit it
		var connection = new Ext.data.Connection().request({
			url: 'actions.php',
			method: 'POST',
			params: {'action': 'create_temp_image', 'image': this.image},
			scope: this,
			success: function(o) {
				var response = Ext.util.JSON.decode(o.responseText);
				
				// If a temporary image could not be created, spit an error
				if (response.success == true) {
					this.imageWidth = response.width;
					this.imageHeight = response.height;
					this.imageProportions = response.width / response.height;
					
					Ext.getCmp('resize_width').setValue(this.imageWidth);
					Ext.getCmp('resize_height').setValue(this.imageHeight);
				} else {
					// Set a status bar message
					Ext.getCmp('status_bar').setStatus({
						text: response.message,
						iconCls: 'save_warning_icon',
						clear: true
					});
					
					this.hide();
				}
			}
		});
		
		var resize_form = new Ext.form.FormPanel({
			url: 'actions.php',
			method: 'POST',
			layout: 'column',
			bodyStyle: 'background-color: #E4E4E4; padding: 5px;',
			border: false,
			hidden: true,
			items: [{
				width: 90,
				layout: 'form',
				bodyStyle: 'background-color: #E4E4E4;',
				border: false,
				labelWidth: 40,
				defaults: {
					width: 40
				},
				items: [{
					xtype: 'numberfield',
					id: 'resize_width',
					name: 'resize_width',
					fieldLabel: 'Width',
					allowBlank: false,
					value: this.imageWidth,
					listeners: {
						scope: this,
						'change': function() {
							if (Ext.getCmp('lock_proportions').pressed == true) {
								Ext.getCmp('resize_height').setValue(Math.round(Ext.getCmp('resize_width').getValue() / this.imageProportions));
							}
						}
					}
				},{
					xtype: 'numberfield',
					id: 'resize_height',
					name: 'resize_height',
					fieldLabel: 'Height',
					allowBlank: false,
					value: this.imageHeight,
					listeners: {
						scope: this,
						'change': function() {
							if (Ext.getCmp('lock_proportions').pressed == true) {
								Ext.getCmp('resize_width').setValue(Math.round(Ext.getCmp('resize_height').getValue() * this.imageProportions));
							}
						}
					}
				}]
			},{
				width: 25,
				bodyStyle: 'background-color: #E4E4E4; padding-top: 13px;',
				border: false,
				items: [{
					xtype: 'button',
					id: 'lock_proportions',
					tooltip: 'Lock Proportions',
					iconCls: 'unlock_button',
					fieldLabel: 'Lock Proportions',
					enableToggle: true,
					handler: function() {
						if (this.pressed == true) {
							this.setIconClass('lock_button');
							Ext.QuickTips.register({
								target: Ext.getCmp('lock_proportions').getEl().child(Ext.getCmp('lock_proportions').buttonSelector),
								text: 'Unlock Proportions'
							});
						} else {
							this.setIconClass('unlock_button');
							Ext.QuickTips.register({
								target: Ext.getCmp('lock_proportions').getEl().child(Ext.getCmp('lock_proportions').buttonSelector),
								text: 'Lock Proportions'
							});
						}
					}
				}]
			},{
				xtype: 'button',
				text: 'Apply Changes',
				scope: this,
				handler: function() {
					this.imageWidth = Ext.getCmp('resize_width').getValue();
					this.imageHeight = Ext.getCmp('resize_height').getValue();
						
					var connection = new Ext.data.Connection().request({
						url: 'actions.php',
						method: 'POST',
						params: {'action': 'resize_image', 'image': this.image, 'resize_width': Ext.getCmp('resize_width').getValue(), 'resize_height': Ext.getCmp('resize_height').getValue()},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success === true) {
								Ext.getDom('image_iframe').contentWindow.location.reload();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						}
					});
				}
			}]
		});
		
		var rotate_form = new Ext.form.FormPanel({
			url: 'actions.php',
			method: 'POST',
			bodyStyle: 'background-color: #E4E4E4; padding: 5px',
			border: false,
			labelWidth: 50,
			hideLabels: true,
			hidden: true,
			items: [{
				xtype: 'radio',
				id: 'rotate_degrees',
				name: 'rotate_degrees',
				boxLabel: '90 Degrees',
				inputValue: '90'
			},{
				xtype: 'radio',
				name: 'rotate_degrees',
				boxLabel: '180 Degrees',
				inputValue: '180'
			},{
				xtype: 'radio',
				name: 'rotate_degrees',
				boxLabel: '270 Degrees',
				inputValue: '270'
			},{
				xtype: 'button',
				text: 'Apply Changes',
				scope: this,
				handler: function() {
					// First swap the proportions on the resize form
					if (Ext.getCmp('rotate_degrees').getGroupValue() == 90 || Ext.getCmp('rotate_degrees').getGroupValue() == 270) {
						var tempWidth = this.imageHeight;
						var tempHeight = this.imageWidth;
						
						this.imageWidth = tempWidth;
						this.imageHeight = tempHeight;
						this.imageProportions = this.imageWidth / this.imageHeight;
						
						Ext.getCmp('resize_width').setValue(this.imageWidth);
						Ext.getCmp('resize_height').setValue(this.imageHeight);
					}
					
					// Now send the ajax request
					var connection = new Ext.data.Connection().request({
						url: 'actions.php',
						method: 'POST',
						params: {'action': 'rotate_image', 'image': this.image, 'rotate_degrees': Ext.getCmp('rotate_degrees').getGroupValue()},
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success === true) {
								Ext.getDom('image_iframe').contentWindow.location.reload();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						}
					});
				}
			}]
		});
		
		var crop_form = new Ext.form.FormPanel({
			url: 'actions.php',
			method: 'POST',
			bodyStyle: 'background-color: #E4E4E4; padding: 5px',
			border: false,
			labelWidth: 40,
			defaults: {
				width: 40
			},
			hidden: true,
			items: [{
				xtype: 'button',
				text: 'Apply Changes',
				scope: this,
				handler: function() {
				}
			}]
		});

		Ext.apply(this, {
			layout: 'border',
			tbar: [{
				text: 'Save',
				tooltip: 'Save Image',
				iconCls: 'save_button',
				scope: this,
				handler: function() {
					var connection = new Ext.data.Connection().request({
						url: 'actions.php',
						method: 'POST',
						params: {'action': 'save_image', 'image': this.image},
						scope: this,
						success: function(o) {
							var response = Ext.util.JSON.decode(o.responseText);
							
							if (response.success === true) {
								this.hide();
							} else {
								// Set a status bar message
								Ext.getCmp('status_bar').setStatus({
									text: response.message,
									iconCls: 'save_warning_icon',
									clear: true
								});
							}
						}
					});
				}
			},{
				text: 'Resize',
				tooltip: 'Resize Image',
				iconCls: 'resize_button',
				handler: function() {
					resize_form.show();
					rotate_form.hide();
					crop_form.hide();
				}
			},{
				text: 'Rotate',
				tooltip: 'Rotate Image',
				iconCls: 'rotate_button',
				handler: function() {
					resize_form.hide();
					rotate_form.show();
					crop_form.hide();
				}
			}/*,{
				text: 'Crop',
				tooltip: 'Crop Image',
				iconCls: 'crop_button',
				handler: function() {
					resize_form.hide();
					rotate_form.hide();
					crop_form.show();
					
					// Now create a crop tool
					var resizer = new Ext.Resizable('element-id', {
						handles: 'all',
						//minWidth: this.image_width,
						//minHeight: 100,
						maxWidth: this.imageWidth,
						maxHeight: this.imageHeight,
						pinned: true
					});
				}
			}*/],
			items: [{
				region: 'center',
				layout: 'anchor',
				border: false,
				items: [{
					anchor: '100% 100%',
					border: false,
					html: '<iframe id="image_iframe" name="image_iframe" style="height: 100%; width: 100%;" frameborder="0" allowtransparency="true" src="image.php?image=' + this.image + '"></iframe>'
				}]
			},{
				id: 'east-region',
				region: 'east',
				width: 125,
				bodyStyle: 'background-color: #E4E4E4;',
				border: false,
				items: [
					resize_form,
					rotate_form
				]
			}]
		});
		
		Ext.ux.ImageEditor.superclass.initComponent.call(this);
	},
	
	afterHide: function() {
		// Send a request to delete the temp image
		var connection = new Ext.data.Connection().request({
			url: 'actions.php',
			method: 'POST',
			params: {'action': 'delete_temp_image', 'image': this.image},
			success: function(o) {
				var response = Ext.util.JSON.decode(o.responseText);
				
				// If a temporary image could not be created, spit an error
				if (response.success == true) {
				} else {
					// Set a status bar message
					Ext.getCmp('status_bar').setStatus({
						text: response.message,
						iconCls: 'save_warning_icon',
						clear: true
					});
				}
			}
		});
		
		Ext.ux.ImageEditor.superclass.afterHide.call(this);
	}
});

//Ext.reg('ImageEditor', Ext.ux.ImageEditor);