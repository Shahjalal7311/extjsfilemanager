<?
// Start the session for this page
session_start();
header("Cache-control: private");

// Include main config file
include ("includes/config.inc.php");

// Include common functions
include ("includes/functions.inc.php");

// Setup some variables
if ($_REQUEST['directory']) {
	$directory = DIRECTORY . $_REQUEST['directory'];
} else {
	$directory = DIRECTORY;
}

$dir = opendir($directory);
$i = 0;

// Get a list of all the files in the directory
while ($temp = readdir($dir)) {
	if (stristr($temp, '_fm_')) continue; // If this is a temp file, skip it.
	if ($_POST['images_only'] && !preg_match('/\.(jpeg|jpg|gif|png)$/', $temp)) continue; // If it isnt an image, skip it
	if (is_dir($directory . "/" . $temp)) continue; // If its a directory skip it
	
	$results[$i]['name'] = $temp;
	$results[$i]['size'] = filesize($directory . '/' . $temp);
	$results[$i]['type'] = filetype($directory . '/' . $temp);
	$results[$i]['permissions'] = format_permissions(fileperms($directory . '/' . $temp));
	$results[$i]['ctime'] = filectime($directory . '/' . $temp);
	$results[$i]['mtime'] = filemtime($directory . '/' . $temp);
	$results[$i]['owner'] = fileowner($directory . '/' . $temp);
	$results[$i]['group'] = filegroup($directory . '/' . $temp);
	$results[$i]['relative_path'] = str_replace(DIRECTORY, '', $directory) . '/' . $temp;
	$results[$i]['full_path'] = $directory . '/' . $temp;
	$results[$i]['web_path'] = WEB_DIRECTORY . str_replace(DIRECTORY, '', $directory) . '/' . $temp;
	$i++;
}

if (is_array($results)) {
	$data['count'] = count($results);
	$data['data'] = $results;
} else {
	$data['count'] = 0;
	$data['data'] = '';
}

print json_encode($data);
?>