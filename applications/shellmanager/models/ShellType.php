<?php

class ShellType extends Table implements ISingleton
{

	protected  $_name = 'shell_type';
	protected  $_rowClass = 'ShellTypeRow';


	protected $_referenceMap    = array(
		'ShellType' => array(
			'columns'           => 'id',
			'refTableClass'     => 'Shell',
			'refColumns'        => 'shell_type_id',
		)
	);

	/**
	 * @var Shell
	 */
	private static $_instance;

	/**
	 * Disallow cloning
	 */
	private function __clone() {
		throw new Exception('Clone is not allowed.');
	}

	public function __construct() {
		parent::__construct();
	}
	/**
	 * @return Shell
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function fetchByKey($key)
	{
		$select = $this->select();
		$select->where('`key` = ?', $key);
		return $this->fetchRow($select);
	}
}
