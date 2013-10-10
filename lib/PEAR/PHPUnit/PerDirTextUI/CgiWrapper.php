<?php

/**
 * Simple wrapper which allows to run command-line tests
 * in the browser. 
 */
class PHPUnit_PerDirTextUI_CgiWrapper
{
    private static $_cmdScriptPath;
    
    /**
     * Run tests in the browser.
     * You must specify the path to the main command-line
     * executor PHPUnit_PerDirTextUI_Command.  
     */
    public static function main($cmdScriptPath)
    {
        self::$_cmdScriptPath = $cmdScriptPath;
        
        chdir(dirname($cmdScriptPath));
        define('PHPUnit_PerDirTextUI_Command_NO_RUN', 1);
        include $cmdScriptPath;
        
        // Read argument.
        $file = @$_GET['file'];
        
        // Prepare options.
        $files = PHPUnit_PerDirTextUI_Command::getTestFiles();
        $options = array();
        foreach ($files as $path => $name) {
            $options[] = '<option ' . ($file == $name? 'selected="selected" ' : '') . 'value="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</option>';
        }
        
        echo '
	        <html>
	        <head>
	            <title>PHPUnit tests</title>
	        </head>
	        <body>
	            <form>
	            <select name="file" onchange="this.form.submit()">
	                <option value="">- All tests -</options>
	                ' . join("\n", $options) . '
	            </select>
	            <input type="submit" value="Run">
	            </form>
	    ';
	    if ($file !== null) self::_run($file);
	    echo '
	        </body>
	        </html>
        ';
    }
    
    private static function _run($file)
    {
        // Call via passthru(). If call via mod_php, PHPUnit sometimes
        // generates segmentation fault. :-(
        echo "<xmp>";
        passthru("php " . escapeshellarg(self::$_cmdScriptPath) . " " . escapeshellarg($file) . " 2>&1");
        echo "</xmp>"; 
    }
}
