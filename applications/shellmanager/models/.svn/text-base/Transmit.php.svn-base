<?php

class Transmit extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected $_name = 'l_transmit';
	protected $_rowClass = 'TransmitRow';
	protected $_referenceMap    = array(
		'Shell' => array(
			'columns'           => 'id',
			'refTableClass'     => 'Shell',
			'refColumns'        => 'transmit_id',
		)
	);

	/**
	 * @var Transmit
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
	 * @return Transmit
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}