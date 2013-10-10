<?php

set_time_limit ( 0 );

/*
 * Copy all the project folder from $from_path to $toPath
 * and only $projectName project from applications folder is copied.
 * Allows to finalizeProject.
 */
function rec_copy($from, $to, $projectName) {
	// If folder not exists - create it
	if (! is_dir ( $to )) {
		mkdir ( $to );
	}

	// cheking if we are in /applications folder
	$appPosition = strpos ( $from, 'applications' );

	if ($appPosition == (strlen ( $from ) - 1 - strlen ( 'applications' ))) {
		$inAppFolder = TRUE;
	} else {
		$inAppFolder = FALSE;
	}

	//copying all included folders and files from $from
	if (is_dir ( $from )) {
		chdir ( $from );
		$handle = opendir ( '.' );
		while (($file = readdir ( $handle )) !== false) {
			//skipping all files like .project and .svn and upper folders
			if (preg_match('/^\./', $file) && $file != '.htaccess') {
				continue;
			}
			if ($file == 'config.local.ini') {
				continue;
			}

			if (is_dir ( $file )) {
				// if we meet some folders in /applications exept $projectName
				// ignore them.
				if ($inAppFolder && $file != $projectName) {
					continue;
				}
				rec_copy($from . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR, $to . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR, $projectName);
				chdir($from);
			}
			if (is_file ( $file )) {
				copy($from . DIRECTORY_SEPARATOR . $file, $to . DIRECTORY_SEPARATOR . $file);
			}
		}
		closedir ( $handle );
	}
}

$projectName = $_GET ['project'];
$to_dir = "e:\srv\hosts\dev";
$from_dir = "e:\home\asm\dev";
rec_copy ( $from_dir, $to_dir, $projectName );
rec_copy ( $to_dir.DIRECTORY_SEPARATOR.'applications'.DIRECTORY_SEPARATOR.$projectName.DIRECTORY_SEPARATOR.'htdocs',$to_dir.DIRECTORY_SEPARATOR.'htdocs','erwf3rwefertwf44qwe');
