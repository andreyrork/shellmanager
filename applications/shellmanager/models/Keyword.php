<?php

class Keyword extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected  $_name = 'keyword';
	protected  $_rowClass = 'KeywordRow';

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