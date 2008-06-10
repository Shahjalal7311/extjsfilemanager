<?
// Include main config file
include ("includes/config.inc.php");

// Include common functions
include ("includes/functions.inc.php");

$data = get_directory_contents(DIRECTORY, true);

print json_encode($data);
?>