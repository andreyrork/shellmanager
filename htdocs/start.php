<?php
require_once '../classes/bootstrap.php';

try {
	// Get front controller instance
	$front = Zend_Controller_Front::getInstance();
	// Set modular structure directories
	$front->addModuleDirectory(dirname(__FILE__) . '/../applications/' . constant('PROJECT') . '/');
	$front->setDefaultModule('default');

	// Create router
	$router = new Zend_Controller_Router_Rewrite();
	// Set front controller router
	$front->setRouter($router);

	// Add routes from config
	Config::getInstance()->addCustomRoutes();

	// Init Db connection
	Db::getInstance();

	// Switch off error handler plugin
	$front->setParam('noErrorHandler', true);
	// Throw exceptions in dispatch loop
	$front->throwExceptions(true);

	// Set return response flag
	$front->returnResponse(true);

	Zend_Layout::startMvc();

	// Create request object
	$request = new Zend_Controller_Request_Http();

	$front->registerPlugin(new DefaultCharsetPlugin());
	// Register form parser plugin
	$front->registerPlugin(new FormParserPlugin(), 3);

	// Dispatch request
	$response = $front->dispatch($request);

	// Output response
	$response->sendResponse();




} catch (Exception $e) {
	// Show exception
	require 'error_lowlevel.php';
}
