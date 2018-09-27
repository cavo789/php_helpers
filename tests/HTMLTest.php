<?php

namespace cavo789;

/**
 * Run this script from the command prompt :
 *		php HTMLTest.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\HTML as HTML;
use \tests\Helpers\Utilities as Utilities;

/**
 * Run the tests
 */

echo Utilities::out('Check cavo789\Helpers\HTML', true);

echo Utilities::out('* makeLink ==> ' . HTML::makeLink('www.google.be', 'Google', ['class' => 'link']));
echo Utilities::out('* getCurrentURL ==> ' . HTML::getCurrentURL());
echo Utilities::out('* addURLParameter ==> ' . HTML::addURLParameter(['var' => '5', 'var2' => 'test']));
echo Utilities::out('* getParameter ==> action=' . HTML::getParameter('action', 'default', 'string', INPUT_GET));
echo Utilities::out('* getFormVariable ==> action=' . HTML::getFormVariable('action', 'default', 'string'));
echo Utilities::out('* addCSSTag [style.css] ==> ' . HTML::addCSSTag('style.css'));
echo Utilities::out('* addJSTag [script.js] ==> ' . HTML::addJSTag('script.js'));
echo Utilities::out('* isAjaxRequest ? ==> ' . (HTML::isAjaxRequest() ? 'Yes' : 'No'));

$html = '<!-- a comment --><h1>Test</h1><!-- something else -->';
echo Utilities::out('* removeHTMLComments [' . $html . '] ==> ' . HTML::removeHTMLComments($html));

$csv = "col1;col2;col3\nrow1-1;row1-2;row1-3;\nrow2-1;row2-2;row2-3";
echo Utilities::out(PHP_EOL . '* csv2table; simple');
echo Utilities::out(HTML::csv2table($csv));

echo Utilities::out(PHP_EOL . '* csv2table; enhanced');
echo Utilities::out(HTML::csv2table($csv, ['enhanced' => true]));

echo Utilities::out(PHP_EOL . '* csv2table; enhanced, ID, class, style');
echo Utilities::out(HTML::csv2table(
	$csv,
	[
		'enhanced' => true,
		'id' => 'tblTest',
		'class' => 'table table-hover table-bordered table-striped dataTable',
		'style' => 'background-color:red;font-size:3em;',
		'role' => 'grid',
		'data-attr' => 'MyAwesomeAttribute'
	]
));
