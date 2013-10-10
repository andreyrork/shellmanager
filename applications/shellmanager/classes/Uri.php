<?php

abstract class Uri extends Zend_Uri
{
	public static function factory($uri = 'http')
    {
        // Separate the scheme from the scheme-specific parts
        $uri            = explode(':', $uri, 2);
        $scheme         = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) === true ? $uri[1] : '';

        if (strlen($scheme) === 0) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('An empty string was supplied for the scheme');
        }

        // Security check: $scheme is used to load a class file, so only alphanumerics are allowed.
        if (ctype_alnum($scheme) === false) {
            require_once 'Zend/Uri/Exception.php';
            throw new Zend_Uri_Exception('Illegal scheme supplied, only alphanumeric characters are permitted');
        }

        /**
         * Create a new Zend_Uri object for the $uri. If a subclass of Zend_Uri exists for the
         * scheme, return an instance of that class. Otherwise, a Zend_Uri_Exception is thrown.
         */
        switch ($scheme) {
            case 'http':
                // Break intentionally omitted
            case 'https':
                $className = 'Zend_Uri_Http';
                break;
            case 'ftp':
                $className = 'Uri_Ftp';
                break;
            case 'mailto':
                // TODO
            default:
                require_once 'Zend/Uri/Exception.php';
                throw new Zend_Uri_Exception("Scheme \"$scheme\" is not supported");
                break;
        }

        Zend_Loader::loadClass($className);
        $schemeHandler = new $className($scheme, $schemeSpecific);

        return $schemeHandler;
    }
}