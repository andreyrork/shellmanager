<?php

class Uri_Ftp extends Zend_Uri_Http
{
    public static function fromString($uri)
    {
        if (is_string($uri) === false) {
            throw new InvalidArgumentException('$uri is not a string');
        }

        $uri            = explode(':', $uri, 2);
        $scheme         = strtolower($uri[0]);
        $schemeSpecific = isset($uri[1]) === true ? $uri[1] : '';

        if (in_array($scheme, 'ftp') === false) {
            throw new Zend_Uri_Exception("Invalid scheme: '$scheme'");
        }

        $schemeHandler = new Uri_Ftp($scheme, $schemeSpecific);
        return $schemeHandler;
    }
}