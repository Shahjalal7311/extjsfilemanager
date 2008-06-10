<?
// Start the session for this page
session_start();
header("Cache-control: private");

// Turn off error reporting
error_reporting(0);

// Include main config file
include ("includes/config.inc.php");

// Include common functions
include ("includes/functions.inc.php");

// Include image transform class
include ("includes/classes/imagetransform.class.php");

if ($_REQUEST['action'] == 'download') {
	$_POST['action'] = $_REQUEST['action'];
	$_POST['directory'] = $_REQUEST['directory'];
}

if ($_POST['directory']) {
	$directory = DIRECTORY . $_POST['directory'];
} else {
	$directory = DIRECTORY;
}

switch ($_POST['action']) {
	case 'upload' :
		// Upload a file to this directory
		foreach ($_FILES as $file) {
			if (is_uploaded_file($file['tmp_name'])) {
				// Set the filename for the uploaded file
				$filename = $directory . "/" . $file['name'];
			
				if (file_exists($filename) == true) {
					// File already exists \\
					#########################
					print '{"success": false, "message": "' . $_POST['directory'] . $file['name'] . ' already exists"}';
					break;
				} else if (copy($file['tmp_name'], $filename) == false) {
					// File can not be copied \\
					############################
					print '{"success": false, "message": "Could not upload' . $file['name'] . '"}';
					break;
				} else {
					print '{"success": true, "message": "Upload complete"}';
				}
			}
		}
		exit();
		break;
	case "download" :
		if ($directory && $_REQUEST['file'] && is_file($directory . "/" . $_REQUEST['file'])) {
			header("Content-type: application/x-download");
			header("Content-Disposition: attachment; filename=\"" . $_REQUEST['file'] . "\";");
			header("Content-Length: " . filesize($directory . "/" . $_REQUEST['file']));
			
			print file_get_contents($directory . "/" . $_REQUEST['file']);
		}
		exit();
		break;
	case "rename" :
		if ($_POST['file'] && is_file($directory . "/" . $_POST['file'])) {
			if (rename($directory . "/" . $_POST['file'], $directory . "/" . $_POST['new_name'])) {
				print '{"success": true, "message": "File renamed successfully"}';
			} else {
				print '{"success": false, "message": "Could not rename ' . $_POST['file'] . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not rename ' . $_POST['file'] . '"}';
		}
		exit();
		break;
	case "chmod" :
		if ($_POST['file'] && is_file($directory . "/" . $_POST['file'])) {
			// First calculate our permissions
			if ($_POST['owner_read']) {
				$owner_perms += 4;
			}
			if ($_POST['owner_write']) {
				$owner_perms += 2;
			}
			if ($_POST['owner_execute']) {
				$owner_perms += 1;
			}
			
			if ($_POST['group_read']) {
				$group_perms += 4;
			}
			if ($_POST['group_write']) {
				$group_perms += 2;
			}
			if ($_POST['group_execute']) {
				$group_perms += 1;
			}
			
			if ($_POST['everyone_read']) {
				$everyone_perms += 4;
			}
			if ($_POST['everyone_write']) {
				$everyone_perms += 2;
			}
			if ($_POST['everyone_execute']) {
				$everyone_perms += 1;
			}
			
			$permissions = 0 . $owner_perms . $group_perms . $everyone_perms;
			
			if (chmod($directory . "/" . $_POST['file'], octdec($permissions))) {
				print '{"success": true, "message": "File chmod\'d successfully ' . $permissions . '"}';
			} else {
				print '{"success": false, "message": "Could not chmod ' . $_POST['file'] . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not chmod ' . $_POST['file'] . '"}';
		}
		exit();
		break;
	case "delete" :
		if ($_POST['file'] && is_file($directory . "/" . $_POST['file'])) {
			if (unlink($directory . "/" . $_POST['file'])) {
				print '{"success": true, "message": "File deleted successfully"}';
			} else {
				print '{"success": false, "message": "Could not delete ' . $_POST['file'] . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not delete ' . $_POST['file'] . '"}';
		}
		exit();
		break;
	case "move" :
		if ($_POST['file'] && $_POST['new_directory'] && is_file($directory . '/' . $_POST['file']) && !file_exists(DIRECTORY . $_POST['new_directory'] . '/' . $_POST['file'])) {
			if (rename($directory . '/' . $_POST['file'], DIRECTORY . $_POST['new_directory'] . '/' . $_POST['file'])) {
				print '{"success": true, "message": "File moved successfully"}';
			} else {
				print '{"success": false, "message": "Could not move ' . $_POST['file'] . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not move ' . $_POST['file'] . '"}';
		}
		exit();
		break;
	case "new_directory" :
		if ($directory && $_POST['new_directory']) {
			if (mkdir($directory . "/" . $_POST['new_directory'])) {
				print '{"success": true, "message": "Directory created successfully"}';
			} else {
				print '{"success": false, "message": "Could not create ' . $_POST['new_directory'] . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not create ' . $_POST['new_directory'] . '"}';
		}
		exit();
		break;
	case "rename_directory" :
		if ($directory && $_POST['new_name']) {
			if (rename($directory, substr($directory, 0, strrpos($directory, "/")) . "/" . $_POST['new_name'])) {
				print '{"success": true, "message": "Directory renamed successfully"}';
			} else {
				print '{"success": false, "message": "Could not rename ' . $directory . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not rename ' . $directory . '"}';
		}
		exit();
		break;
	case "chmod_directory" :
		if ($directory && $_POST['permissions']) {
			if (chmod($directory, octdec(0 . $_POST['permissions']))) {
				print '{"success": true, "message": "Directory chmod\'d successfully"}';
			} else {
				print '{"success": false, "message": "Could not chmod ' . $directory . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not chmod ' . $directory . '"}';
		}
		exit();
		break;
	case "delete_directory" :
		if ($_POST['directory'] && $directory != DIRECTORY && stristr($directory, DIRECTORY)) {
			if (rmdir_r($directory)) {
				print '{"success": true, "message": "Directory deleted successfully"}';
			} else {
				print '{"success": false, "message": "Could not delete ' . $directory  . '"}';
			}
		} else {
			print '{"success": false, "message": "Could not delete ' . $directory . '"}';
		}
		exit();
		break;
	case "create_temp_image" :
		if ($_POST['image']) {
			// Create a temp image copy of the image we are trying to edit
			$temp_image = str_replace(basename($_POST['image']), '_fm_' . basename($_POST['image']), $_POST['image']);
			if (copy(DIRECTORY . $_POST['image'], DIRECTORY . $temp_image)) {
				list($width, $height) = getimagesize(DIRECTORY . $temp_image);
				print '{"success": true, "message": "Temporary image created successfully", "width": "' . $width . '", "height": "' . $height . '"}';
			} else {
				print '{"success": false, "message": "Error: Could not create temporary image"}';
			}
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
	case "delete_temp_image" :
		if ($_POST['image']) {
			// Delete our temp image
			$temp_image = str_replace(basename($_POST['image']), '_fm_' . basename($_POST['image']), $_POST['image']);
			if (unlink(DIRECTORY . $temp_image)) {
				print '{"success": true, "message": "Image successfully deleted"}';
			} else {
				print '{"success": false, "message": "Error: Temporary image could not be deleted"}';
			}
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
	case "save_image" :
		if ($_POST['image']) {
			// Overwrite our original image with our temp image
			$temp_image = str_replace(basename($_POST['image']), '_fm_' . basename($_POST['image']), $_POST['image']);
			if (copy(DIRECTORY . $temp_image, DIRECTORY . $_POST['image'])) {
				print '{"success": true, "message": "Image successfully saved"}';
			} else {
				print '{"success": false, "message": "Error: Could not save image"}';
			}
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
	case "resize_image" :
		if ($_POST['image']) {
			// Make sure we are editing our temp image, and not the original
			$temp_image = str_replace(basename($_POST['image']), '_fm_' . basename($_POST['image']), $_POST['image']);
			
			$image = new imageTransform();
			$image->jpegOutputQuality = 80;
			$image->sourceFile = DIRECTORY . $temp_image;
			$image->targetFile = DIRECTORY . $temp_image;
			$image->resizeToWidth = $_POST['resize_width'];
			$image->resizeToHeight = $_POST['resize_height'];
			if ($image->resize()) {
				print '{"success": true, "message": "Image successfully resized"}';
			} else {
				print '{"success": false, "message": "Error: Could not resize image"}';
			}
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
	case "rotate_image" :
		if ($_POST['image']) {
			// Make sure we are editing our temp image, and not the original
			$temp_image = str_replace(basename($_POST['image']), '_fm_' . basename($_POST['image']), $_POST['image']);
			
			$image = new imageTransform();
			$image->jpegOutputQuality = 80;
			$image->sourceFile = DIRECTORY . $temp_image;
			$image->targetFile = DIRECTORY . $temp_image;
			if ($image->rotate(-$_POST['rotate_degrees'])) { // Rotate in negative degrees so it goes clockwise
				print '{"success": true, "message": "Image successfully rotated"}';
			} else {
				print '{"success": false, "message": "Error: Could not rotate image"}';
			}
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
	case "crop_image" :
		if ($_POST['image']) {
			print '{"success": true, "message": "Image successfully cropped"}';
		} else {
			print '{"success": false, "message": "Error: No image specified"}';
		}
		exit();
		break;
}
?>