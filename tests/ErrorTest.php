<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php StringsTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Classes\Error as Error;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Classes\Error', true);

echo Utilities::out('A RuntimeException error will be triggered with "Action not supported" ' . PHP_EOL .
	'as message, the HTML template below will be used by the Error class');

$error = new Error("<h1>Houston we' ve a problem</h1>" . PHP_EOL .
	'<h2>Error {{ error_code }} - {{ error_title }}</h2>' . PHP_EOL .
	'<div>{{ error_message }}</div>' . PHP_EOL .
	'<hr/>' . PHP_EOL .
	'<div>Please email us</div>');

throw new \RuntimeException('Action not supported');
