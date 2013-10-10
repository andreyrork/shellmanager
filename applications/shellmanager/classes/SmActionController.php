<?php

class SmActionController extends ActionController
{
	public function preDispatch()
	{
		$this->view->document['css'][] = 'base.css';
		$this->view->document['js'][] = 'menuslide.js';
	}
}