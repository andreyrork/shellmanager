<?php

class LinkPart extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected  $_name = 'link_part';
	protected  $_rowClass = 'LinkPartRow';

	/**
	 * @var LinkPart
	 */
	private static $_instance;


	public function __construct() {
		parent::__construct();
	}
	/**
	 * @return LinkPart
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}


}