<?php

class IndexController extends SmActionController
{
	public function indexAction()
	{

		$shell = Shell::getInstance()->find(4)->current();
		$remote = RemoteShell::factory($shell);
		var_dump($remote->write());

	}
}


