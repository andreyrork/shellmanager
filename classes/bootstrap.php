<?php

define('PROJECT', 'shellmanager');

/**
 * This file has to be included in every PHP script to initialize
 * the execution environment.
 */
class Bootstrap
{
	public static function execute()
	{
		// Initialize include path
		$root = dirname(dirname(__FILE__));
		$includePath = str_replace(array(';', ':'), PATH_SEPARATOR, get_include_path());

		$project = self::getProject();
		$projectPath = $root . DIRECTORY_SEPARATOR
			. 'applications' . DIRECTORY_SEPARATOR
			. $project . DIRECTORY_SEPARATOR;

		$controllers = glob(
			$projectPath
			. '*' . DIRECTORY_SEPARATOR
			. 'controllers'
		);



		set_include_path(
			$includePath . PATH_SEPARATOR .
			// Adding controllers to include path
			implode(PATH_SEPARATOR, $controllers) . PATH_SEPARATOR .
			// Adding static paths to include path
			implode(PATH_SEPARATOR, array(
				dirname(__FILE__),
				// Path to current project models
				$projectPath . 'models',
				// Path to current project classes
				$projectPath . 'classes',
				$root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR,
				$root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PEAR' . DIRECTORY_SEPARATOR,
				$root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'other' . DIRECTORY_SEPARATOR,
		)));

		// Initialize autoloading.
		require_once 'Autoload.php';
	}

	/**
	 * Try to get project name from several ways
	 * @return string
	 */
	public static function getProject()
	{
		// If project hardcoded - return project name
		if (defined('PROJECT')) {
			return constant('PROJECT');
		}

		// Otherwise try to get project name from request
		$match = array();
		preg_match('~\/([a-z]+)~', $_SERVER['REQUEST_URI'], $match);
		if (isset($match[1])) {
			$applications = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'applications';
			if (is_dir($applications . DIRECTORY_SEPARATOR . $match[1])) {
				define('PROJECT', $match[1]);
				return $match[1];
			}
		}

		// If project from request dont exists use default
		define('PROJECT', 'dummy');
		return 'dummy';
	}
}

Bootstrap::execute();
