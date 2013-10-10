<?php

class TaskController extends SmActionController
{
    public function indexAction()
    {
		$this->view->document['title'] = 'Index of tasks';
		$this->view->document['js'][] = 'check.js';
		$this->view->document['js'][] = 'task.js';

		// Create custom select query for tasks
		$task = Task::getInstance();
		$select = $task->select();

		// Apply custom ordering by Request
		if ($column = $this->_getParam('order')) {
			$select->order($column);
		}

		// By default ordering by modified date
		$select->order('modified desc');

		// Query
        $rowset = $task->fetchAll($select);

        // Process rowset
        $tasks = array();
        foreach ($rowset as $row) {
            $array = array();
            $array = $row->toArray();

            // Get dependent shell information
            $shell = $row->findDependentRowset('Shell')->current();
            $array['shell'] = $shell->toArray();
            $uri = $array['shell']['url'];
            $uri = Uri::factory($uri);
            $array['shell']['url'] = $uri->getHost();

            // Get dependent action information
            $shell = $row->findDependentRowset('Action')->current();
            $array['action'] = $shell->toArray();

            $tasks[] = $array;
		}

		// Complex task information going to view
		$this->view->task = $tasks;

		$this->_actionListForming();
    }

    /**
     * Forward checked task ids to delete or run action
     * @return forward delete || run
     */
    public function processAction()
    {
    	if ($this->_getParam('delete')) {
    		$this->_forward('delete');
    	}

    	if ($this->_getParam('run')) {
    		$this->_forward('run');
    	}
    }

    /**
     * Creating tasks by multiple shells with multiple actions
     * @return redirect indexAction
     */
    public function _multipleAdd()
    {
    	// Getting shell rowset by ids from request
		$shell = $this->_getParam('shell_id');
		$shellRowset = Shell::getInstance()->find($shell);

		// Getting action rowset by ids from request
		$action = $this->_getParam('action_id');
		$actionRowset = Action::getInstance()->find($action);

		// For each shell adding each action task
		foreach ($shellRowset as $shellRow) {
			foreach($actionRowset as $actionRow) {
				$task = Task::getInstance()->createRow();

				$task->shell_id = $shellRow->id;
				$task->action_id = $actionRow->id;
				$task->save();
			}
		}

		$this->_goto(array('controller' => 'task', 'action' => 'index'), null, true);
    }

    public function itemAction()
    {
    	$this->view->document['title'] = 'Task edit';

    	$task = $this->_getTask();
		$this->view->task = $task->toArray();

		if ($this->_request->isPost()) {
			// For creating specified function
			if ($task->id === null) {
				$this->_multipleAdd();
				return;
			}

			$shell = $this->_getParam('shell_id');
			$action = $this->_getParam('action_id');

			$task->shell_id = $shell;
			$task->action_id = $action;
			$task->save();

			// Redirect for not repost form
			$this->_goto(array('id' => $task->id));
		}

		$this->_urlListForming();
		$this->_actionListForming();
    }

	public function deleteAction()
	{
		$id = $this->_getParam('id');
		$rowset = Task::getInstance()->find($id);
		foreach ($rowset as $row) {
			$row->delete();
		}

		$this->_goto(array('controller' => 'task', 'action' => 'index'), null, true);
	}

	/**
	 * Getting Task object by id in Request
	 *
	 * @return Task|null
	 */
	private function _getTask()
	{
		// Get id from Request
		$id = $this->_getParam('id');

		// If id not specified return
		if ($id == 0) {
			return Task::getInstance()->createRow();
		}

		// For successfully integer we try to return existing shell row
		$task = Task::getInstance()->find($id)->current();

		// If row not specified we cant further process
		if (!$task instanceof TaskRow) {
			$this->_goto(array('action' => 'index'));
		}

		return $task;
	}

	/**
	 * Set to view full shell url list
	 * @return void
	 */
	private function _urlListForming()
	{
		$rowset = Shell::getInstance()->fetchAll();

		$array = array();
		foreach ($rowset as $row) {
			if (!$row->isSuccess()) {
				continue;
			}

			$id = $row->__get('id');
			$array[ $id ] = $row->__get('url');
		}

		$this->view->shell = $array;
	}

	/**
	 * Set to view full action key list
	 * @return void
	 */
	private function _actionListForming()
	{
		$rowset = Action::getInstance()->fetchAll();
		$array = array();

		foreach ($rowset as $row) {
			$array[$row->id] = $row->key;
		}

		$this->view->action = $array;
	}

	/**
	 * Processing task list
	 * @return void
	 */
	public function runAction()
	{
		$this->getFrontController()->setParam('noViewRenderer', true);
		$this->_helper->layout()->disableLayout();
		$this->_ajaxOnly();

		$result = null;
		$e = null;
		try {
			$task = $this->_getTask();

			$shell = $task->findDependentRowset('Shell')->current();
			$action = $task->findDependentRowset('Action')->current();

			$result = TaskManager::getInstance()->process($task);
		} catch (Exception $e) {
		}

		$message = '';
		if ($result) {
			$class = 'success';
		} else {
			$class = 'failure';
			if ($e) {
				$message = $e->getMessage();
			} else {
				$message = $shell->__get('debug');
			}
		}

		if ($message) {
			$url = $shell->__get('url');
			$uri = Zend_Uri::factory($url);

			$act= $action->__get('key');

			$message = $uri->getHost() . ' : ' . $act . ' : ' . $message;
		}

		$this->_setJson(array(
			'class' => $class,
			'error' => $message,
		));
		return;
	}

}
