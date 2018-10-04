<?php

declare(strict_types=1);

namespace cavo789;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

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
			"Test Template\n" .
			"<link rel=\"stylesheet\" href=\"style.css\" media=\"screen\"/>\n" .
			"Hello, this is my nice content\n" .
			'<script src="js/jquery.js"></script>';

		$this->assertTrue($expected == $temp->show('login', $arrVariables));

		// When using "raw" mode, we didn't expect to have CSS and JS
		// These lines should disappear
		$temp->setMode('raw');

		$expected =
			"Test Template\n" .
			"\n" .
			'Hello, this is my nice content';
		$this->assertTrue($expected == $temp->show('login', $arrVariables));
	}
}
