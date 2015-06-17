<?php
/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 14.11.12
 * @time 17:34
 */

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

require_once 'PHPUnit/TextUI/TestRunner.php';

// Include the composer autoloader
require dirname(__DIR__) . '/vendor/autoload.php';