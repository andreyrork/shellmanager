<?php

class Task extends Table implements ISingleton
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected  $_name = 'task';
	protected  $_rowClass = 'TaskRow';

	protected $_dependentTables = array('Shell', 'Action');

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


	/**
	 * Return task by Shell and action.
	 * Create new task if now exists.
	 *
	 * @param ShellRow
	 * @param string|ActionRow
	 * @return TaskRow
	 */
	public function selectByShellAndAction($shell, $action)
	{
		if (!$action instanceof ActionRow) {
			$action = Action::getInstance()->fetchByKey($action);
		}

		$select = $this->select();
		$select->where('`shell_id` = ?', $shell->__get('id'));
		$select->where('`action_id` = ?', $action->__get('id'));
		$task = $this->fetchRow($select);

		if (!$task) {
			$task = $this->createRow(array(
				'shell_id' => $shell->__get('id'),
				'action_id' => $action->__get('id')
			));
			$task->save();
		}

		return $task;
	}
}