<?php

class TransmitRandomizer_Just2 implements TransmitRandomizer_Interface
{
	public function randomize($transmit)
	{
		$links = explode(PHP_EOL, $transmit->__get('text'));
		$rowset = Keyword::getInstance()->fetchAll();

		$exceptions = array();
		foreach ($rowset as $row) {
			$exceptions[] = $row->__get('key');
		}
		foreach ($links as &$link) {
			$link = trim($link);
			if (!$link) {
				unset($link);
				continue;
			}

			$matches = array();
			preg_match('/\?search\=(.+)/i', $link, $matches);
			$key = isset($matches[1]) ? $matches[1] : '';

			$title = array();
			$anchor = array();
			foreach ($exceptions as $exception) {
				if (false !== stripos($key, $exception)) {
					$title[] = $exception;
					$anchor[] = $exception;
					$key = str_replace($exception, '', $key);
					break;
				}
			}

			$words = explode('-', $key);

			foreach ($words as $word) {
				if (rand(0, 1)) {
					$title[] = $word;
				}

				if (rand(0, 1)) {
					$anchor[] = $word;
				}
			}

			shuffle($title);
			shuffle($anchor);
			$title = implode(' ', $title);

			$anchor = implode(' ', $anchor);
			$link = "<a href='$link' title='$title'>$anchor</a>";
		}

		$result = implode(PHP_EOL, $links);
		$tagRandomizator = new TransmitRandomizer_Just0();
		return $tagRandomizator->_randomize($result);
	}
}