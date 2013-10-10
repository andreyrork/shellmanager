<?php

class ShellStatus extends Table implements ISingleton
{
	const ERROR_URL = 'url';
	const ERROR_RESPONSE = 'response';
	const ERROR_INTERNAL = 'remote_shell';
	const ERROR_PATH = 'path';
	const ERROR_UNDEFINED = 'undefined';
	const OK = 'ok';

	protected  $_name = 'shell_status';
	protected  $_rowClass = 'ShellStatusRow';


	protected $_referenceMap    = array(
		'ShellStatus' => array(
			'columns'           => 'id',
			'refTableClass'     => 'Shell',
			'refColumns'        => 'status_id',
		)
	);

	/**
	 * @var Shell
	 */
	private static $_instance;

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
