<?php

class Config implements ISingleton
{
	const FILE_GLOBAL = '/global.ini';
	const FILE_LOCAL = '/config.local.ini';

	/**
	 * Config strict
	 * @var Zend_Config
	 */
	private $_config;

	/**
	 * @var Config
	 */
	private static $_instance;

	/**
	 * Disallow cloning
	 */
	private function __clone() {
		throw new Exception('Clone is not allowed.');
	}

	/**
	 * @return Config
	 */
	private function __construct()
	{
		$path = dirname(__FILE__) . '/../applications/' . constant('PROJECT');

		// Load global config
		$configPath = $path . self::FILE_GLOBAL;
		if (is_readable($configPath)) {
			$config = new Zend_Config_Ini($configPath, null, true);
		} else {
			throw new Exception('Cannot read config.ini file: ' . $configPath);
		}

		// Merge global config with local
		$configPath = $path . self::FILE_LOCAL;
		if (is_readable($configPath)) {
			$config->merge(new Zend_Config_Ini($configPath));
		}

		$this->_config = $config;
	}

	/**
	 * @return Config
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Handles parameters reading magically.
	 *
	 * @param string $name  Property name.
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->_config->__get($name);
	}


	/**
	 * Config modifications is not allowed.
	 * Always throws an exception.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	private function __set($name, $value)
	{
		// Multi-level assignment is denied by Zend_Config itself.
		throw new Exception('Config is read-only');
	}

	/**
	 * Create routes out of config.ini configuration
	 *
	 * Example INI:
	 * [routes]
	 * archive.route = "archive/:year/*"
	 * archive.defaults.controller = archive
	 * archive.defaults.action = show
	 * archive.defaults.year = 2000
	 * archive.reqs.year = "\d+"
	 *
	 * news.type = "Zend_Controller_Router_Route_Static"
	 * news.route = "news"
	 * news.defaults.controller = "news"
	 * news.defaults.action = "list"
	 */
	public function addCustomRoutes()
	{
		$file = dirname(__FILE__) . '/../applications/' . constant('PROJECT') . self::FILE_GLOBAL;
		if (is_readable($file)) {
			foreach (new Zend_Config_Ini($file, 'routes') as $name => $info) {
				$route = Zend_Controller_Router_Route::getInstance($info);
				Zend_Controller_Front::getInstance()->getRouter()->addRoute($name, $route);
			}
		}
	}

}