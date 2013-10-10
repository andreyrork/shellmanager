<?php

abstract class Table extends Zend_Db_Table_Abstract
{
	protected $_rowClass = 'Row';
	protected $_rowsetClass = 'Rowset';

	public function fetchByKey($key)
	{
		$select = $this->select();
		$select->where('`key` = ?', $key);
		return $this->fetchRow($select);
	}
}