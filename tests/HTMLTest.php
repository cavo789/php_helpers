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
