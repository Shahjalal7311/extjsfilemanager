<?
// Start the session for this page
session_start();
header("Cache-control: private");

// Include main config file
include ("includes/config.inc.php");

// Include common functions
include ("includes/functions.inc.php");

// Make sure we are using the temp image and not the original
$image = str_replace(basename($_REQUEST['image']), '_fm_' . basename($_REQUEST['image']), $_REQUEST['image']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Image</title>
<style>
html, body {
	background: url(images/transparent_grid.png);
	height: 100%;
	margin: 0px;
}
</style>
</head>

<body>
	<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td align="center" valign="middle"><img src="<? print WEB_DIRECTORY.$image; ?>?<? print rand(); ?>" /></td>
	</tr>
	</table>
</body>
</html>
