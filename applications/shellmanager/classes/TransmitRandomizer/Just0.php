<?php

class TransmitRandomizer_Just0 implements TransmitRandomizer_Interface
{
	public function randomize($transmit)
	{
		$text = $transmit->__get('text');
		return $this->_randomize($text);
	}

	public function _randomize($text)
	{
		$text = strip_tags($text, '<a>');
		$text = explode('</a>',$text);
		foreach ($text as $key => $t) {
			$text[$key] = trim($t);
			if (!$t) {
				unset($text[$key]);
			}
		}

		shuffle($text);
		$resultContent = '';

		$rate = Config::getInstance()->transmit->tag_rate;
		foreach ($text as $link) {
			if (rand(1, $rate) == $rate) {
				$tag = $this->_getTag(Tag::KEY_TAG_COMMON);
			} else {
				$tag = $this->_getTag('empty');
			}
			$resultContent .= sprintf("%s%s</a>%s\n", $tag['open'], $link, $tag['close']);
		}

		$hide = $this->_getTag(Tag::KEY_TAG_HIDE);
		return $hide['open'] . $resultContent . $hide['close'];
	}


	/**
	 * Return tag by string option (empty or key from table Tag.
	 *
	 * @param string $option
	 * @return array array('open'=>'','close'=>'')
	 */
	private function _getTag($option)
	{
		if ($option == 'empty') {
			return array('open' => '', 'close' => '');
		}

		$select = Tag::getInstance()->select();
		$select->where('`key`=?', $option);
		$select->order('rand()');
		$tag = Tag::getInstance()->fetchRow($select);

		if ($tag) {
			return array('open' => $tag->open, 'close' => $tag->close);
		}

		return $this->_getTag('empty');
	}
}