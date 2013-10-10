<?php

class Db implements ISingleton
{
	/**
	 * Database default adapter
	 * @var Zend_Db_Adapter_Abstract
	 */
	private static $_adapter;

	/**
	 * @var Db
	 */
	private static $_instance;

	/**
	 * Disallow cloning
	 */
	private function __clone() {
		throw new Exception('Clone is not allowed.');
	}

	/**
	 * @return Db
	 */
	private function __construct()
	{
		self::$_adapter = Zend_Db::factory(Config::getInstance()->connection);
		Zend_Db_Table_Abstract::setDefaultAdapter(self::$_adapter);
		self::$_adapter->query('set names utf8');
	}

	/**
	 * @return Db
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Return adapter from config.ini
	 *
	 * @return Zend_Db_Adapter_Abstract
	 */
	public static function getConnection()
	{
		if (!self::$_adapter) {
			self::getInstance();
		}

		return self::$_adapter;
	}

}