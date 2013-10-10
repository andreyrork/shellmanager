<?php
// Create temporary directory.
$tmpFile = tempnam('non-existed', '');
unlink($tmpFile);
$tmpDir = dirname($tmpFile) . "/coverage-" . getmyuid();
if (file_exists($tmpDir)) {
    unlink_recurse($tmpDir);
}
@mkdir($tmpDir, 0777);

// Run the code coverage.
$_SERVER['argv'][] = '--coverage-html';
$_SERVER['argv'][] = $tmpDir;
register_shutdown_function('runBrowser');
include "run.php";

/**
 * Runs the browser and open $tmpDir/index.html in it.
 * OS-specific code is here...
 */
function runBrowser()
{
    global $tmpDir;
    if (getenv("COMSPEC")) {
        system("start \"\" \"$tmpDir/index.html\"");
    } else {
        system("firefox \"$tmpDir/index.html\" & >/dev/null 2>&1 &");
    }
}

/**
 * Recurrent unlink().
 * 
 * @param string $file
 * @return void
 */
function unlink_recurse($file) 
{
    if (is_dir($file) && !is_link($file)) {
        foreach (glob($file . '/*') as $sf) {
            if (!unlink_recurse($sf) ) {
                trigger_error("Failed to remove $sf\n");
                return false;
            }
        }
        return rmdir($file);
    } else {
        return unlink($file);
    }
}
