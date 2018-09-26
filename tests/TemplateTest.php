<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php TemplateTest.php
 */

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\Template as Template;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\Template', true);

echo Utilities::out('Read the ' . __DIR__ . DS . 'Templates' . DS . 'login.html, ' .
	'replace variables and return his HTML');

$temp = new Template('html', __DIR__ . '/Templates');

$arrVariables = [
	'title' => 'Test Template',
	'content' => 'Hello, this is my nice content',
	'css' => '',
	'js' => ''
];

echo $temp->show('login', $arrVariables);
