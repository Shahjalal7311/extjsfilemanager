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
<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0" style="background: url(images/transparent_grid.png);">
<tr>
<td align="center" valign="middle"><div id="wrapper" style="display: inline-block;"><img src="<? print WEB_DIRECTORY.$image; ?>?<? print rand(); ?>" /></div></td>
</tr>
</table>
