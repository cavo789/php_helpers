<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php AppTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\App as App;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\App', true);

$folder = __DIR__ . '/logs';

// ------------------------
// 1. Enable the debug mode
echo Utilities::out('Initialize the debug mode, create folder [' . $folder . ']');
App::initDebugMode(true, $folder);

// Raise a notice, display it and record it
echo Utilities::out('Use of an undefined variable should raise a Notice, ' .
	'message displayed here below but also registered in the ' .
	$folder . '/error.log file' . PHP_EOL);
echo Utilities::out('* Test is OK when an error is displayed here below *' . PHP_EOL);
echo $undefined;

// -------------------------
// 2. Disable the debug mode
echo Utilities::out(PHP_EOL . PHP_EOL . 'Disabling errors');
App::initDebugMode(false);

// Still use an undefined variable but don't let the Notice message
// raise since debug is no more enabled
echo Utilities::out('Still use an undefined, no notice would be generated here below ' .
	'and no records added in the log since Notices are no more recorded' . PHP_EOL);
echo Utilities::out('* Test is OK when no error is displayed here below *' . PHP_EOL);
echo $undefined;
