<?php

// At first, check php version
$requirementPhpVersion = '5.1.4';
if (version_compare(phpversion(), $requirementPhpVersion, '<')) {
	$errorMessage = 'Fatal error: Current php version is ' 
		. phpversion() 
		. ', requirement is ' 
		. $requirementPhpVersion
		. ', please update your version';
    die($errorMessage);
}

// PHP Extension checker
$listRequiredExtenstions = array('SPL', 'pcre', 'DOM', 'Reflection');
foreach ($listRequiredExtenstions as $ext) {
	if (!extension_loaded($ext)) {
		$errorMessage = 'Fatal error: Requirement PHP Extension not found: ' . $ext;
		die($errorMessage);
	}
}

// Thinking we can bootstrap now and test environment
if (!file_exists('../classes/bootstrap.php')) {
	$errorMessage = 'Fatal error: Bootstrap class not found';
	die($errorMessage);
}

try {
	require_once '../classes/bootstrap.php';
	
	// Test Zend_Framework is autoloaded and we have needed version
	$requirementZfVersion = '1.6.1';
	if (Zend_Version::compareVersion($requirementZfVersion) === 1) {
		$errorMessage = 'Fatal error: Current zf version is ' 
			. Zend_Version::VERSION
			. ', requirement is ' 
			. $requirementZfVersion
			. ', please update your version';
		throw new Exception($errorMessage);
	}
	
	$requirementPhpUnitVersion = '3.3.1';
	if (version_compare(PHPUnit_Runner_Version::id(), $requirementPhpUnitVersion, '<')) {
		$errorMessage = 'Fatal error: Current UnitTest version is ' 
			. PHPUnit_Runner_Version::id()
			. ', requirement is ' 
			. $requirementPhpUnitVersion
			. ', please update your version';
		throw new Exception($errorMessage);
	}
	
} catch(Exception $e) {
	die($e->getMessage());
}

echo "\nEnvironment successfully tested...\n\n";