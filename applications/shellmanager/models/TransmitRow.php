<?php

class TransmitRow extends Row
{
	public function randomize()
	{
		$randomizer = TransmitRandomizer_Factory::factory($this);
		if (!$randomizer instanceof TransmitRandomizer_Interface) {
			return '';
		}

		$result = $randomizer->randomize($this);
		return $result;
	}



}