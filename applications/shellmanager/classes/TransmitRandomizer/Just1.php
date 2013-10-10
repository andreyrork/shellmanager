<?php

class TransmitRandomizer_Just1 implements TransmitRandomizer_Interface
{
	public function randomize($transmit)
	{
		$links = explode(PHP_EOL, $transmit->__get('text'));

		foreach ($links as &$link) {
			$link = trim($link);
			if (!$link) {
				unset($link);
				continue;
			}


			$matches = array();
			preg_match('/\?search\=(.+)/i', $link, $matches);
			$key = isset($matches[1]) ? $matches[1] : '';

			$select = LinkPart::getInstance()->select();
			$select->where('`key` = ?', $key);
			$select->order('rand()');
			$row = LinkPart::getInstance()->fetchRow($select);

			$select = LinkPart::getInstance()->select();
			$select->where('`key` = ?', $key);
			$select->order('rand()');
			$row2 = LinkPart::getInstance()->fetchRow($select);

			$title = '';
			$anchor = '';
			if ($row instanceof Row) {
				$title = $row->__get('title');
				$anchor = $row2->__get('anchor');
			}

			$link = "<a href='$link' title='$title'>$anchor</a>";
		}

		$result = implode(PHP_EOL, $links);
		$tagRandomizator = new TransmitRandomizer_Just0();
		return $tagRandomizator->_randomize($result);
	}
}