<?
// Start the session for this page
session_start();
header("Cache-control: private");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>File Manager | Chooser Examples</title>
<link rel="stylesheet" type="text/css" href="/scripts/ext-2.0/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="/scripts/ext-2.0/resources/css/xtheme-gray.css" />
<link rel="stylesheet" type="text/css" href="css/styles.css" />

<script language="javascript" type="text/javascript" src="/scripts/ext-2.0/adapter/ext/ext-base.js"></script>
<script language="javascript" type="text/javascript" src="/scripts/ext-2.0/ext-all.js"></script>

<script language="javascript" type="text/javascript" src="FileChooser.js"></script>
<script language="javascript" type="text/javascript" src="ImageChooser.js"></script>

<script language="javascript" type="text/javascript">
	Ext.onReady(function(){
		Ext.QuickTips.init();
		Ext.form.Field.prototype.msgTarget = 'side';
		
		// Remove the loading panel after the page is loaded
		Ext.get('loading').remove();
		Ext.get('loading_mask').fadeOut({remove:true});
		
		var form = new Ext.form.FormPanel({
			renderTo: 'form',
			title: 'Chooser Examples',
			url: '',
			method: 'POST',
			bodyStyle: 'padding: 5px',
			labelWidth: 125,
			defaults: {
				width: 250
			},
			items: [{
				xtype: 'trigger',
				id: 'file_chooser',
				name: 'file_chooser',
				fieldLabel: 'File Chooser',
				allowBlank: false,
				triggerClass: 'x-form-file-trigger',
				onTriggerClick: function() {
					chooser = new FileChooser({
						width: 515, 
						height: 400
					});
					
					chooser.show(this, function(el, data) {
						el.setValue(data);
					});
				}
			},{
				xtype: 'trigger',
				id: 'image_chooser',
				name: 'image_chooser',
				fieldLabel: 'Image Chooser',
				allowBlank: false,
				triggerClass: 'x-form-file-trigger',
				onTriggerClick: function() {
					chooser = new ImageChooser({
						width: 515, 
						height: 400
					});
					
					chooser.show(this, function(el, data) {
						el.setValue(data);
					});
				}
			}]
		});
	});
</script>
</head>

<body>
	<div id="loading_mask"></div>
	<div id="loading"> 
		<div id="loading_indicator"><img src="images/loading_indicator.gif" alt="Loading" /> Loading...</div>
	</div>
	
	<div style="padding: 25px;">
		<div id="form"></div>
	</div>
</body>
</html>