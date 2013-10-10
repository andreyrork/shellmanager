<?php

/**
 * Turns on class autoloading.
 */

class Autoload
{
	public static function _autoload($className)
	{
	    $fileName = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    	if (!@fopen($fileName, 'r', true)) {
    		$stack = debug_backtrace();
    		if (!strcasecmp(@$stack[2]['function'], 'class_exists')) {
    			return false;
    		} else {
    			throw new Exception("Cannot autoload $className: no such file $fileName");
    		}
    	}

    	return require_once($fileName);
	}

    public static function _onShutdown()
    {

    }
}

spl_autoload_register(array('Autoload', '_autoload'));
register_shutdown_function(array('Autoload', '_onShutdown'));
