<?php

class UserControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{
	public function testModuleSpecified()
	{
		$this->dispatch('/');
		$this->assertModule('dummy');
	}
	
	public function testControllerSpecified()
	{
		$this->dispatch('/');
		$this->assertController('index');
	}
	
	public function testActionSpecified()
	{
		$this->dispatch('/');
		$this->assertAction('index');
	}
}