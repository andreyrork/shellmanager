<?php

class ShellRow extends Row
{
	/**
	 * Return transmit text related by current shell row
	 *
	 * @return string
	 */
	public function getTransmit()
	{
       $transmit = Transmit::getInstance();
       $transmitrow = $transmit->find($this->transmit_id)->current();
       return $transmitrow->text;
	}

	/**
	 * Return boolean mean shell status valid or not
	 * @return boolean
	 */
	public function isSuccess()
	{
		$status = ShellStatus::getInstance()->fetchByKey(ShellStatus::OK);
		if ($this->status_id == $status->__get('id') && $this->shell_type_id) {
			return true;
		}

		return false;
	}

	/**
	 * Presave checking url for valid.
	 *
     * Saves the properties to the database.
     *
     * This performs an intelligent insert/update, and reloads the
     * properties with fresh data from the table on success.
     *
     * @return mixed The primary key value(s), as an associative array if the
     *     key is compound, or a scalar if the key is single-column.
     */
	public function save()
	{
		parent::save();
	}


	/**
	 * Check shell params and save shell type and status
	 * @return boolean
	 * @throws Zend_Db_Exception
	 * @throws Zend_Uri_Exception
	 * @throws Zend_Http_Exception
	 * @throws RemoteShell_Exception
	 * @throws RemoteShell_FileException
	 */
	public function check()
	{
		try {
			// Check for Url is valid Zend_Uri
			$url = Uri::factory($this->__get('url'));

			$response = null;
			if (!$url instanceof Uri_Ftp) {
				// Check for url available
				$client = new Zend_Http_Client($url, array('timeout' => '5'));

				if ($user = $url->getUsername()) {
					$password = $url->getPassword();
					$client->setAuth($user, $password);
				}

				// Try to detect shell type
				$response = $client->request();
			}

			// Create SmType for test request and save shell type
			$remoteShell = RemoteShell::factory($this, $response);
			$this->__set('shell_type_id', $remoteShell->getShellType()->__get('id'));

			// Check path
			if ($remoteShell->getShellType()->__get('key') != 'old') {
				$remoteShell->getContent();
			}

			$statusKey = ShellStatus::OK;

		} catch (Zend_Uri_Exception $e) {
			$statusKey = ShellStatus::ERROR_URL;
		} catch (Zend_Http_Exception $e) {
			$statusKey = ShellStatus::ERROR_RESPONSE;
		} catch (RemoteShell_Exception $e) {
			$statusKey = ShellStatus::ERROR_INTERNAL;
		} catch (RemoteShell_FileException $e) {
			$statusKey = ShellStatus::ERROR_PATH;
		}

		// Set shell status after checks done
		$status = ShellStatus::getInstance()->fetchByKey($statusKey);
		$this->__set('status_id', $status->__get('id'));

		$debug = null;
		if (isset($e)) {
			// Set debug message if errors happen
			$debug = $e->getMessage();
		}

		$this->__set('debug', $debug);

		if ($statusKey == ShellStatus::OK) {
			return true;
		}

		return false;
	}
}