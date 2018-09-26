<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php StringsTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\Strings as Strings;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\Strings', true);

$value = 'cavo789';
echo Utilities::out('* [' . $value . '] startsWith [cavo]? ==> ' . (Strings::startsWith($value, 'cavo') ? 'Yes' : 'No'));
echo Utilities::out('* [' . $value . '] startsWith [test]? ==> ' . (Strings::startsWith($value, 'test') ? 'Yes' : 'No'));
echo Utilities::out('* [' . $value . '] endsWith [789]? ==> ' . (Strings::endsWith($value, '789') ? 'Yes' : 'No'));
echo Utilities::out('* [' . $value . '] endsWith [test]? ==> ' . (Strings::endsWith($value, 'test') ? 'Yes' : 'No'));

$bad = '"test_/?"';
echo Utilities::out('* cleansing [' . $bad . '] ==> ' . Strings::cleansing($bad));
