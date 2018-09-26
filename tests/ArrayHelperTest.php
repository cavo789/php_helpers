<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php ArrayHelperTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\ArrayHelper as ArrayHelper;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\ArrayHelper', true);

// ------------------------
// 1. Test array2string
echo Utilities::out('* array2string *');
$arr = ['style.css', 'interface.css', 'demo.css'];
echo Utilities::out('Convert an array into a string, one item = one line');
echo Utilities::out('Sample array: [' . implode($arr, ', ') . ']');
echo Utilities::out(PHP_EOL . '   Without processing; just output ' .
	'items:' . PHP_EOL);
echo ArrayHelper::array2string($arr);

echo Utilities::out(PHP_EOL . '   Call a function (like ' .
	'cavo789\Helpers\HTML::addCSSTag()) on each item:' . PHP_EOL);

echo ArrayHelper::array2string($arr, 'cavo789\Helpers\HTML::addCSSTag');

// ------------------------
// 2. Test array_get
echo Utilities::out(PHP_EOL . '* array_get *');

// Create an associative array
$json = '{ "cdn" : { "css" : [ "style.css", "interface.css", "demo.css" ] } }';
$arr = json_decode($json, true);

echo Utilities::out('Content of the $arr table:');
echo print_r($arr, true);

echo Utilities::out(PHP_EOL . 'Use dot notation to retrieve a key; get "cdn.css"');

echo print_r(ArrayHelper::array_get($arr, 'cdn.css'), true);

// ------------------------
// 2. Test transpose
echo Utilities::out(PHP_EOL . '* transpose *');

$json = '';
for ($user = 1; $user <= 3; $user++) {
	$json .= '"User' . $user . '":{ ';
	for ($question = 1; $question <= 3; $question++) {
		$json .= '"Question' . $question . '": "Answer User' . $user .
			' - Q' . $question . '",';
	}
	$json = rtrim($json, ',') . '},';
}
$json = '{ ' . rtrim($json, ',') . '}';
$arr = json_decode($json, true);

echo Utilities::out(PHP_EOL . 'Content of the $arr table before (Users->Questions->Answers):');
echo print_r($arr, true);

echo Utilities::out(PHP_EOL . 'Content of the $arr table after ' .
	'the transpose (Questions->Users->Answers):');

echo print_r(ArrayHelper::transpose($arr), true);
