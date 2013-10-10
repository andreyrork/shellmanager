<?php

class TransmitRandomizer_Factory
{
	/**
	 * Return Randomizer class by TransmitRow
	 *
	 * @param TransmitRow $transmit
	 * @return TransmitRandomizer
	 */
	public static function factory($transmit)
	{
		$type = $transmit->__get('randomize_type');
		if (!$type) {
			return null;
		}

		$class = 'TransmitRandomizer_' . ucfirst(strtolower($type));
		if (!class_exists($class)) {
			return null;
		}

		return new $class;
	}
}