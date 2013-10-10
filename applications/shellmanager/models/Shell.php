<?php

class Shell extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected  $_name = 'l_shell';
	protected  $_rowClass = 'ShellRow';

	protected $_dependentTables = array('Transmit', 'ShellType', 'ShellStatus');

	protected $_referenceMap    = array(
		'Task' => array(
			'columns'           => 'id',
			'refTableClass'     => 'Task',
			'refColumns'        => 'shell_id',
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
}
