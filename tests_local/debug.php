<?php

/**
 * Test the Debug::enable() method; force PHP errors to be displayed on
 * screen.
 */
include '../vendor/autoload.php';

use \cavo789\Helpers\Debug;

Debug::enable();
$i=$i / 0;
