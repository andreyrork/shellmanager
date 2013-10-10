<?php
require_once 'bootstrapTest.php';

PHPUnit_PerDirTextUI_Command::addTestDir(dirname(__FILE__) . '/unit');
if (!defined('PHPUnit_PerDirTextUI_Command_NO_RUN')) {
    PHPUnit_PerDirTextUI_Command::main();
}
