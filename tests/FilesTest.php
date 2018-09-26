<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php FilesTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\Files as Files;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\Files', true);

$folder = __DIR__ . '/testFolder';
echo Utilities::out('* makeFolder ' . $folder);

Files::makeFolder($folder, true);
echo Utilities::out('* exists [' . $folder . ']? ==> ' . (Files::exists($folder) ? 'Yes' : 'No'));

$bad = 'folder/subfolder/\'clean",/';
echo Utilities::out('* sanitize [' . $bad . '] ==> ' . Files::sanitize($bad));
