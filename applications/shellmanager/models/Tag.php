<?php

class Tag extends Table implements ISingleton
{
	const KEY_TAG_COMMON = 'common';
	const KEY_TAG_HIDE = 'hide';

	protected  $_name = 'tag';
	protected  $_rowClass = 'TagRow';

	/**
	 * @var Tag
	 */
	private static $_instance;

	/**
	 * @return Tag
	 */
	public function __construct() {
		parent::__construct();
	}
	/**
	 * @return Tag
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
