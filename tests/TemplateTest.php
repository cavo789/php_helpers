<?php

declare(strict_types=1);

namespace cavo789;

// Autoload files using Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

use \cavo789\Helpers\Template as Template;
use \PHPUnit\Framework\TestCase;

final class TemplateTest extends TestCase
{
	public function testShowTemplate(): void
	{
		// Process the HTML and replace variables and return his HTML
		$temp = new Template('html', __DIR__ . DIRECTORY_SEPARATOR . 'Templates');

		$arrVariables = [
			'title' => 'Test Template',
			'content' => 'Hello, this is my nice content',
			'css' => '<link rel="stylesheet" href="style.css" media="screen"/>',
			'js' => '<script src="js/jquery.js"></script>'
		];

		// When using "html" mode, we expect to have CSS and JS
		$expected =
			"<!DOCTYPE html>\n" .
			"<html>\n\n\n\n" .
			"<head>\n" .
			"	<meta charset=\"utf-8\">\n" .
			"	<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n" .
			"	<title>Test Template</title>\n" .
			"	<link rel=\"stylesheet\" href=\"http:/assets/css/interface.css\">\n" .
			"</head>\n\n\n" .
			"<body class=\"hold-transition login-page\">\n" .
			"	Hello, this is my nice content\n" .
			"</body>\n\n" .
			'</html>';

		$this->assertTrue($expected == $temp->show('login', $arrVariables));

		// But when using "raw" mode, we didn't expect to have CSS and JS
		// These lines should disappear
		$temp->setMode('raw');

		$expected =
			"<!DOCTYPE html>\n" .
			"<html>\n\n\n\n" .
			"<body class=\"hold-transition login-page\">\n" .
			"	Hello, this is my nice content\n" .
			"</body>\n\n" .
			'</html>';

		$this->assertTrue($expected == $temp->show('login', $arrVariables));
	}
}
