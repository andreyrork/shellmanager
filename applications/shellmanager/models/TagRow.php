<?php

class TagRow extends Row
{
	/**
	 * Validate key
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
		if ($this->key != Tag::KEY_TAG_COMMON && $this->key != Tag::KEY_TAG_HIDE) {
			throw new Zend_Db_Exception('Tag key must be only `'
				. Tag::KEY_TAG_COMMON . '` or `'
				. Tag::KEY_TAG_HIDE
				. '`'
			);
		}

		parent::save();
	}
}