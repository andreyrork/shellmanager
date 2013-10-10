<?php

class TransmitController extends SmActionController
{
	public function indexAction()
	{
		$this->view->document['title'] = 'Index of transmit';
		$this->view->document['js'][] = 'crud.js';

		$rowset = Transmit::getInstance()->fetchAll();
		$this->view->transmit = $rowset->toArray();
	}

	public function itemAction()
	{
		$this->view->document['title'] = 'Editing transmit';

		$transmit = $this->_getTransmit();

		$array = $transmit->toArray();
		$array['randomize'] = $transmit->randomize();
		$this->view->transmit = $array;

		// If form sended process input data
		if ($this->_request->isPost()) {
			$transmit->key = $this->_getParam('key');
			$transmit->text = stripslashes($this->_getParam('text'));
			$transmit->__set('randomize_type', $this->_getParam('randomize_type'));
			$transmit->save();

			// Redirect for not repost form
			$this->_goto(array('id' => $transmit->id));
		}


	}

	public function deleteAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->getFrontController()->setParam('noViewRenderer', true);
		$this->_ajaxOnly();

		try {
			$transmit = $this->_getTransmit();
			$transmit->delete();
		} catch (Exception $e) {
			$this->_setJson(array('result' => 'Error: ' . $e->getMessage()));
			return ;
		}

		$this->_setJson(array('result' => 'success'));
		return ;
	}

	/**
	 * Getting Transmit object by id in Request
	 *
	 * @return TransmitRow
	 */
	private function _getTransmit()
	{
		// Get id from Request
		$id = $this->_getParam('id');

		// If id not specified creating new row
		if ($id == 0) {
			return Transmit::getInstance()->createRow();
		}

		// For successfully integer we try to return existing transmit row
		$transmit = Transmit::getInstance()->find($id)->current();

		// If row not specified we cant further process
		if (!$transmit instanceof Row) {
			$this->_goto(array('action' => 'index'));
		}

		return $transmit;
	}
}