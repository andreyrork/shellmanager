<?php

require_once '../classes/bootstrap.php';

if (!isset($_GET['path'])) {
	die('Path not specified');
}

if (!isset($_GET['ext'])) {
	die('Extension not specified');
}

$contentTypes = array(
	'css' => 'text/css',
	'js' => 'text/javascript',
	'png' => 'image/png',
	'gif' => 'image/gif',
	'jpg' => 'image/jpg',
);

$path = $_GET['path'];
$ext = $_GET['ext'];

if (!isset($contentTypes[$ext])) {
	die('Content-type not specified');
}

header('Content-Type: ' . $contentTypes[$ext]);
$path = dirname(__FILE__) . '/../applications/' . PROJECT . '/htdocs/' . $path;
if (!file_exists($path)) {
	die('File not exists');
}

echo file_get_contents($path);
