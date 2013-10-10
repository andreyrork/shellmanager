<?php

class ActionController extends Zend_Controller_Action
{
	/**
	 * MetaForm instance
	 *
	 * @var HTML_MetaForm
	 */
	private $_metaForm;

	/**
	 * MetaFormAction instance
	 *
	 * @var HTML_MetaFormAction
	 */
	private $_metaFormAction;

	public function init()
	{
		parent::init();

		// Prepare array for document services
		$this->view->document = array();
		$this->view->document['js'] = array(
			'jQuery' => 'jquery.js'
		);
		$this->view->document['css'] = array(
		);

		$this->view->baseUrl = $this->getFrontController()->getBaseUrl();
		$module = $this->getFrontController()->getRequest()->getModuleName();
		$controller = $this->getRequest()->getControllerName();
		$this->view->addScriptPath(dirname(__FILE__) . "/../applications/" . PROJECT . "/$module/views/scripts/$controller");
	}

	/**
	 * Redirect to a route-based URL
	 *
	 * Uses route's assemble method tobuild the URL; route is specified by $name;
	 * default route is used if none provided.
	 *
	 * @param  array $urlOptions Array of key/value pairs used to assemble URL
	 * @param  string $name
	 * @param  boolean $reset
	 */
	protected function _goto(array $urlOptions = array(), $name = null, $reset = false)
	{
		$this->_helper->redirector->gotoRoute($urlOptions, $name, $reset);
	}

	/**
	 * Set JSON data to response body
	 *
	 * @param array $data
	 * @return void
	 */
	protected function _setJson($data)
	{
		$this->getResponse()->setBody(Zend_Json::encode($data));
	}

	/**
	 * Throw exception for non ajax calls
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function _ajaxOnly()
	{
		if (!$this->getRequest()->isXmlHttpRequest()) {
			throw new Exception('This is AJAX-only action');
		}

	}

	/**
	 * Return MetaForm instance
	 *
	 * @return HTML_MetaForm
	 */
	protected function _getMetaForm()
	{
		if (!$this->_metaForm) {
			$this->_metaForm = new HTML_MetaForm(md5(PROJECT));
		}

		return $this->_metaForm;
	}

	/**
	 * Return MetaFormAction instance
	 *
	 * @return MetaFormAction
	 */
	protected function _getMetaFormAction()
	{


		if (!$this->_metaFormAction) {
			$this->_metaFormAction = new MetaFormAction($this->_getMetaForm());
		}
		return $this->_metaFormAction;
	}
}