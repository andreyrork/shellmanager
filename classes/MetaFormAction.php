<?php

class MetaFormAction extends HTML_MetaFormAction
{
	const INIT = 'INIT';
	const SUBMIT = 'UNKNOWN';

	/**
	 * Return all processed form values
	 * @return array
	 */
	public function getValues()
	{
		$array = $this->metaForm->getFormMeta();
		return $array['value'];
	}


	protected function _processMeta($meta)
	{
		if (!isset($meta['id']) && !isset($meta['name'])) {
			$meta['id'] = $meta['name'] = 'unknown';
		}

		if (!isset($meta['id'])) {
			$meta['id'] = $meta['name'];
		}

		if (!isset($meta['name'])) {
			$meta['name'] = $meta['id'];
		}

		if (!isset($meta['label'])) {
			$meta['label'] = $meta['id'];
		}

		if (!isset($meta['maxlen'])) {
			$meta['maxlen'] = 0;
		}

		if (!isset($meta['minlen'])) {
			$meta['minlen'] = 0;
		}

		return $meta;

	}

	public function validator_require($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		if (trim($value) === '') {
			return array('message' => sprintf('Поле `%s` обязательно для заполнения.', $meta['label']));
		}

		return true;
	}

	public function validator_length($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		if (trim($value) === '') {
			return true;
		}

		if ($meta['minlen']) {
			if (mb_strlen($value) < $meta['minlen']) {
				return array(
					'message' => sprintf(
						'Длина поля `%s` должна превышать `%u` символа(ов).',
						$meta['label'], $meta['minlen']
				));
			}
		}

		if ($meta['maxlen']) {
			if (mb_strlen($value) > $meta['maxlen']) {
				return array(
					'message' => sprintf(
						'Длина поля `%s` НЕ должна превышать `%u` символа(ов).',
						$meta['label'], $meta['maxlen']
				));
			}
		}

		return true;
	}

	public function validator_varchar($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		if (preg_match('/^[a-z0-9]*$/is', trim($value))) {
			return true;
		}

		return array(
			'message' => sprintf(
				'Поле `%s` должно содержать только латинские символы и цифры.',
				$meta['label']
		));
	}

	public function validator_date($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		if (strtotime($value) === false) {
			return array(
				'message' => sprintf(
					'Поле `%s` должно содержать дату в правильном формате.',
					$meta['label']
			));
		}

		return true;
	}

	public function validator_email($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		if (!preg_match('/^.+@.+\.[a-z]{2,5}$/i', $value)) {
			return array(
				'message' => sprintf(
					'Поле `%s` должно содержать электронный адрес в правильном формате.',
					$meta['label']
			));
		}

		return true;
	}

	public function validator_password($value, $meta)
	{
		$meta = $this->_processMeta($meta);

		$namespace = new Zend_Session_Namespace('metaform_password');
		if (!isset($namespace->password)) {
			$namespace->password = $value;
			return true;
		}

		$oldPassword = $namespace->password;
		$namespace->unsetAll();

		if ($value == $oldPassword) {
			return true;
		}

		return array('message' => 'Введенные пароли не совпадают');
	}


}