<?php 

/**
 * Run this script from the command prompt :
 *		php test.php
 */

 
require_once dirname(__DIR__).'/vendor/autoload.php'; // Autoload files using Composer autoload

use cavo789\ArrayHelper as ArrayHelper;
use cavo789\Files as Files;
use cavo789\HTML as HTML;
use cavo789\Strings as Strings;

function out(string $str, bool $isTitle = false) {
	if ($isTitle) {
		echo PHP_EOL;
	}
	
	echo $str.PHP_EOL;
	
	if ($isTitle) {
		echo str_repeat('-', strlen($str)).PHP_EOL;		
	}
}

function testArrayHelper() {
	
	echo out('Check ArrayHelper class', true);
	
	$arr = ['style.css', 'interface.css', 'demo.css'];
	echo '*	array2string, convert ['.implode($arr, ',').'] '.
		'items into links '.PHP_EOL.ArrayHelper::array2string($arr, 'cavo789\HTML::addCSSTag');
}

function testFiles() {
	echo out('Check Files class', true);
	
	$folder = __DIR__.'/testFolder';
	echo out('* makeFolder ' . $folder);
	
	Files::makeFolder($folder, true);
	echo out('* exists ['.$folder.']? ==> ' . (Files::exists($folder) ? 'Yes' : 'No'));
	
	$bad = 'folder/subfolder/\'clean",/';
	echo out('* sanitize ['.$bad.'] ==> '.Files::sanitize($bad));
	
}

function testHTML() {
	echo out('Check HTML class', true);
	echo out('* makeLink - '.HTML::makeLink('www.google.be', 'Google', ["class"=>"link"]));	
}

function testStrings() {
	echo out('Check Strings class', true);
	$value = 'cavo789';
	echo out('* ['.$value.'] startsWith [cavo]? ==> '.(Strings::startsWith($value, 'cavo') ? 'Yes' : 'No'));
	echo out('* ['.$value.'] startsWith [test]? ==> '.(Strings::startsWith($value, 'test') ? 'Yes' : 'No'));
	echo out('* ['.$value.'] endsWith [789]? ==> '.(Strings::endsWith($value, '789') ? 'Yes' : 'No'));
	echo out('* ['.$value.'] endsWith [test]? ==> '.(Strings::endsWith($value, 'test') ? 'Yes' : 'No'));
	$bad = '"test_/?"';
	echo out('* cleansing ['.$bad.'] ==> '.Strings::cleansing($bad));	
}

/**
 * Run the tests
 */
 
echo out('Check cavo789 helpers');
echo out(str_repeat("=", 21));

testArrayHelper();
//testFiles();
//testHTML();
//testStrings();



