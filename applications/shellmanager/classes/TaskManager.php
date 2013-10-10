<?php

class TaskManager implements ISingleton
{
	/**
	 * @var TaskManager
	 */
	private static $_instance;

	/**
	 * @return TaskManager
	 */
	private function __construct()
	{}

	/**
	 * @return TaskManager
	 */
	public static function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Run sequence task process.
	 *
	 * @return void
	 */
	public function run()
	{
		while($task = Task::getInstance()->getNext()) {
			$this->process($task);
		}
	}

	/**
	 * Process single task
	 *
	 * @param TaskRow $task
	 * @return boolean
	 */
	public function process($task)
	{
		if (!$task instanceof TaskRow) {
			throw new Exception('Cant process invalid TaskRow');
		}

		$shell = $task->findDependentRowset('Shell')->current();
		$action = $task->findDependentRowset('Action')->current();
		$method = $action->__get('key');

		$remoteShell = RemoteShell::factory($shell);
		$result = $remoteShell->$method();

		if ($result) {
			$result = 1;
		} else {
			$result = 0;
		}

		$task->__set('status', $result);
		$task->save();

		// Plugin for automatic create task for write where check if failure
		if (Config::getInstance()->plugin->autowrite) {
			if ($action->__get('key') == 'check' && $result == 0 && !$shell->__get('debug')) {
				Task::getInstance()->selectByShellAndAction($shell, 'write');
			}
		}


		return $result;
	}


}
