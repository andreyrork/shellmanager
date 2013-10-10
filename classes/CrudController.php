<?php

class CrudController extends ActionController
{
	/**
	 * Specified table instance
	 * @var Table
	 */
	private $_table;

	public function preDispatch()
	{
		parent::preDispatch();
		//$this->view->document['js'][] = 'crud.js';
	}

	public function indexAction()
	{
		$this->view->document['title'] = 'List of tables';
		$this->view->table = Db::getConnection()->listTables();
	}

	public function listAction()
	{
		$table = $this->_getTable();
		$this->view->document['title'] = 'List of table rows: ' . $table->info('name');

		$this->view->columns = $table->info('cols');
		$this->view->rows = $table->fetchAll()->toArray();
	}

	/**
	 * Get Table object by Request param where full table name.
	 * Also adding table columns to view.
	 *
	 * @return Table
	 */
	protected function _getTable()
	{
		if ($this->_table instanceof Table) {
			return $this->_table;
		}

		$table = $this->_getParam('table');

		// Remove l_ from the beginning (tempolary)
		$table = str_replace('l_', '', $table);

		// Ucfirst and _ remove
		$table = str_replace('_', ' ', $table);
		$table = ucwords($table);
		$table = str_replace(' ', '', $table);

		if (class_exists($table)) {
			return call_user_func(array($table, 'getInstance'));
		}

		$this->_goto(array('controller' => 'mysql', 'action' => 'index'), null, true);
	}

	public function itemAction()
	{
		$row = $this->_getRow();
		$this->view->row = $row->toArray();

		$table = $row->getTable();
		$columns = $table->info(Zend_Db_Table::METADATA);
		$this->view->columns = $columns;
		//var_dump($columns);die();
		$this->view->dependent = $this->_getDependent();


		$this->view->document['title'] = 'Update just one row from ' . $table->info('name');

		// If form sended process input data
		if ($this->_request->isPost()) {
			try {
				foreach ($columns as $column) {
					$c = $column['COLUMN_NAME'];

					// If we have uploaded file with same the column name we use him content for value
					if (isset($_FILES[$c])) {
						$value = file_get_contents($_FILES[$c]['tmp_name']);
						$ext = strtolower(array_pop(explode('.',$_FILES[$c]["name"])));
						$this->_setParam('extension', $ext);
						$this->_setParam('mime', mime_content_type($_FILES[$c]["name"]));
					} else {
						$value = $this->_getParam($c);
					}

					$row->__set($c, $value);
				}

				$row->save();
			} catch (Zend_Db_Exception $e) {
				throw $e;
			}

			// Redirect for not repost form
			$this->_goto(array('action' => 'list'));
		}
	}

	/**
	 * Process all dependent columns and return array with reference columns and values
	 * @return array
	 */
	private function _getDependent()
	{
		$table = $this->_getTable();
		$tableName = ucfirst($table->info(Zend_Db_Table::NAME));

		$dependentTables = $table->info(Zend_Db_Table::DEPENDENT_TABLES);
		$result = array();
		foreach ($dependentTables as $tableClass) {
			$dependentTable = call_user_func(array($tableClass, 'getInstance'));
			$map = $dependentTable->info(Zend_Db_Table::REFERENCE_MAP);
			// @todo $map2 = $dependentTable->getReference($tableName);
			// @todo try to findDependentRowset and other method of Table

			$refColumn = $map[$tableName]['refColumns'];
			$dependentColumnName = $map[$tableName]['columns'];

			$dependentColumns = $dependentTable->info(Zend_Db_Table::COLS);
			if (in_array('name', $dependentColumns)) {
				$dependentColumnValue = 'name';
			} else if (in_array('key', $dependentColumns)) {
				$dependentColumnValue = 'key';
			} else {
				$dependentColumnValue = $dependentColumnName;
			}

			$dRowset = $dependentTable->fetchAll();
			$tmp = array();
			foreach ($dRowset as $dRow) {
				$tmp[ $dRow->__get($dependentColumnName) ] = $dRow->__get($dependentColumnValue);
			}

			$result[ $refColumn ] = $tmp;
		}

		return $result;
	}

	public function deleteAction()
	{
		$this->getFrontController()->setParam('noViewRenderer', true);
		Zend_Layout::getMvcInstance()->disableLayout();
		//$this->_ajaxOnly();

		try {
			$row = $this->_getRow();
			$row->delete();
		} catch (Zend_Db_Exception $e) {
			throw $e;
		}

		$this->_goto(array('action' => 'list', 'table' => $this->_getParam('table')));
	}

	/**
	 * Getting Transmit object by id in Request
	 *
	 * @return Row
	 */
	protected function _getRow()
	{
		// Get table
		$table = $this->_getTable();

		// Get id from Request
		$id = $this->_getParam('id');


		// If id not specified creating new row
		if ($id == 0) {
			return $table->createRow();
		}

		// For successfully integer we try to return existing table row
		$row = $table->find($id)->current();

		// If row not specified we cant further process
		if (!$row instanceof Row) {
			$this->_goto(array('action' => 'index'));
		}

		return $row;
	}
}


// Mime-type hack
if(!function_exists('mime_content_type')) {
    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}



   /**
     * ucfirst UTF-8 aware function
     *
     * @param string $string
     * @return string
     * @see http://ca.php.net/ucfirst
     */
    function my_ucfirst($string, $e ='utf-8') {
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) {
            $string = mb_strtolower($string, $e);
            $upper = mb_strtoupper($string, $e);
            preg_match('#(.)#us', $upper, $matches);
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e);
        } else {
            $string = ucfirst($string);
        }
        return $string;
    }