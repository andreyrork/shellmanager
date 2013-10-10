<?php

class Action extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected  $_name = 'action';

	protected $_referenceMap    = array(
		'Task' => array(
			'columns'           => 'id',
			'refTableClass'     => 'Task',
			'refColumns'        => 'action_id',
		)
	);

	/**
	 * @var Transmit
	 */
	private static $_instance;

	public function __construct() {
		parent::__construct();
	}
	/**
	 * @return Transmit
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Getting action row by key
	 *
	 * @param string $key
	 * @return string
	 */
	public function retrieveByKey($key)
	{
		$select = $this->select();
		$select->where('`key` = ?', $key);

		// Execute query and return response or null
		return $this->fetchRow($select);
	}
}