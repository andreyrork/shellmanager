<?php

class ShellController extends SmActionController
{
	public function indexAction()
	{
		$this->view->document['title'] = 'Index of Shell';
		$this->view->document['js'][] = 'check.js';
		$this->view->document['js'][] = 'crud.js';

		// Create custom select query for shells
		$shell = Shell::getInstance();
		$select = $shell->select();

		$status = ShellStatus::getInstance()->fetchByKey(ShellStatus::OK);
		$expr = sprintf("if (`status_id` = %u, 1, 0)", $status->__get('id'));
		$expr = new Zend_Db_Expr($expr);

		/*$columns = array(Zend_Db_Table_Select::SQL_WILDCARD, 'sort' => $expr);
		$select->columns($columns, 'l_shell');*/

		$name = $shell->info(Zend_Db_Table::NAME);
		$columns = array(Zend_Db_Table_Select::SQL_WILDCARD, 'sort' => $expr);
		$select->from($name, $columns);

		$select->order('sort');

		// Apply custom ordering by Request
		if ($column = $this->_getParam('order')) {
			$select->order($column);
		}

		$rowset = $shell->fetchAll($select);

		$shell = array();
        foreach ($rowset as $row) {
			$array = array();
			$array = $row->toArray();
			if ($row->transmit_id) {
				$transmit = $row->findDependentRowset('Transmit')->current();
				$array['transmit'] = $transmit->toArray();
			}

			if ($row->shell_type_id) {
				$type = $row->findDependentRowset('ShellType')->current();
				$array['type'] = $type->toArray();
			}

			$status = $row->findDependentRowset('ShellStatus')->current();
			$array['status'] = $status->toArray();

			$array['success'] = $row->isSuccess();

			$shell[] = $array;
		}

		$this->view->shell = $shell;
	}

	/**
	 * Processing shell list
	 * @return void
	 */
	public function runAction()
	{
		$this->getFrontController()->setParam('noViewRenderer', true);

		// Getting shell id from Request. Must be integer or array or empty.
		$id = $this->_getParam('id');

		// If id is numeric adding it to simple array
		if (is_numeric($id)) {
			$id = array($id);
		}

		// Processing all shells from the array
		if (is_array($id)) {
			// Retrieve rowset by ids
			$shells = Shell::getInstance()->find($id);
			foreach ($shells as $shell) {
				// Run all of selected shells to process
				$shell->check();
				$shell->save();
			}
		}

		$this->_goto(array('controller' => 'shell', 'action' => 'index'), null, true);
	}

	public function itemAction()
	{
		$this->view->document['title'] = 'Shell edit';

		$shell = $this->_getShell();

		$array = $shell->toArray();
		if ($shell->__get('shell_type_id')) {
			$type = $shell->findDependentRowset('ShellType')->current();
			$array['type'] = $type->toArray();
		}

		if ($shell->__get('status_id')) {
			$status = $shell->findDependentRowset('ShellStatus')->current();
			$array['status'] = $status->toArray();
		}

		$this->view->shell = $array;
        $this->_transmitListForming();

		// If form sended process input data
		if ($this->_request->isPost()) {
			try {
				$shell->__set('comment', $this->_getParam('comment'));
				$shell->__set('url', $this->_getParam('url'));
				$shell->__set('path', $this->_getParam('path'));

				$transmitId = $this->_getParam('transmit_id');
				if (!$transmitId) {
					$transmitId = null;
				}

				$shell->transmit_id = $transmitId;

				$shell->check();

				$shell->save();
			} catch (Zend_Db_Exception $e) {
				$this->view->error = array('type' => get_class($e), 'message' => $e->getMessage());
			}


			// Redirect for not repost form
			if (!$e) {
				$this->_goto(array('id' => $shell->id));
			}

		}

	}

	public function multipleTransmitUpdateAction()
	{
		$this->view->document['title'] = 'Multiple Transmit Update';

		if ($this->_request->isPost()) {
			// Find alone selected transmit
			$transmit = $this->_getParam('transmit_id');
			$transmit = Transmit::getInstance()->find($transmit)->current();
			if (!$transmit) {
				$this->_goto(array('action' => 'index'));
			}

			$shell = $this->_getParam('shell');
			$shellRowset = Shell::getInstance()->find($shell);
			foreach ($shellRowset as $shellRow) {
				$shellRow->transmit_id = $transmit->id;
				$shellRow->save();
			}

			$this->_goto(array('action' => 'index'));
		}

		$this->_shellListForming();
		$this->_transmitListForming();
	}

	public function deleteAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->getFrontController()->setParam('noViewRenderer', true);
		$this->_ajaxOnly();

		try {
			$shell = $this->_getShell();

			$tasks = $shell->findParentRow('Task');
			if ($count = count($tasks)) {
				throw new Zend_Db_Exception("That shell have some tasks($count), please delete tasks manually before removing shell");
			}

			$shell->delete();
		} catch (Exception $e) {
			$this->_setJson(array('result' => 'Error: ' . $e->getMessage()));
			return ;
		}

		$this->_setJson(array('result' => 'success'));
		return ;
	}

	public function prAction()
	{
	}

	/**
	 * Getting Shell object by id in Request
	 *
	 * @return ShellRow
	 */
	private function _getShell()
	{
		// Get id from Request
		$id = $this->_getParam('id');

		// If id not specified creating new row
		if ($id == 0) {
			return Shell::getInstance()->createRow();
		}

		// For successfully integer we try to return existing shell row
		$shell = Shell::getInstance()->find($id)->current();

		// If row not specified we cant further process
		if (!$shell instanceof ShellRow) {
			$this->_goto(array('action' => 'index'));
		}

		return $shell;
	}

	private function _shellListForming()
	{
		$rowset = Shell::getInstance()->fetchAll();

		$options = array();
		foreach ($rowset as $row) {
			$options[$row->id] = $row->url;
		}

		$this->view->shell = $options;
	}

	private function _transmitListForming()
	{
		$rowset = Transmit::getInstance()->fetchAll();
   		$options = array('' => '');
		foreach ($rowset as $row) {
			$options[$row->id] = $row->key;
		}

		$this->view->transmit = $options;
	}

}