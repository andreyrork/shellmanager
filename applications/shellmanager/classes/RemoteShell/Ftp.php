<?php

class RemoteShell_Ftp extends RemoteShell
{
	/**
	 * @return ShellType
	 */
	public function getShellType() {
		return ShellType::getInstance()->fetchByKey('ftp');
	}

	/**
	 * Connect to ftp server
	 * @return resourse
	 * @throws RemoteShell_Exception
	 */
	private function _getFtp()
	{
		$url = $this->_getUrl();
		$uri = Uri::factory($url);

		$user = $uri->getUsername();
		$password = $uri->getPassword();
		$host = $uri->getHost();
		$port = $uri->getPort();

		$ftp = ftp_connect($host, $port);
		if (!$ftp) {
			throw new RemoteShell_Exception('Cannot connect to ftp: ' . $host);
		}

		if (!@ftp_login($ftp, $user, $password)) {
			throw new RemoteShell_Exception('Login and password to ftp looks wrong: ' . $host);
		}

		return $ftp;
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
		$ftp = $this->_getFtp();

		$tempFile = tmpfile();
		if (!ftp_fget($ftp, $tempFile, $path, FTP_ASCII)) {
			throw new RemoteShell_Exception('Cannot get file from ftp');
		}

		ftp_close($ftp);


		$content = '';
		fseek($tempFile, 0);
		while (!feof($tempFile)) {
			$content .= fgets($tempFile);
		}

		fclose($tempFile);

		return $content;
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
		$ftp = $this->_getFtp();

		$tempFile = tmpfile();
		fwrite($tempFile, $content);
		fseek($tempFile, 0);

		if (!ftp_fput($ftp, $path, $tempFile, FTP_ASCII)) {
			throw new RemoteShell_Exception('Cannot put file on ftp');
		}

		fclose($tempFile);
		ftp_close($ftp);

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
		throw new Exception('Method not implemented');
	}

	/**
	 * @param Zend_Http_Response Server response
	 * @return string
	 */
	public function getError($response)
	{
		throw new Exception('Method not implemented');
	}
}