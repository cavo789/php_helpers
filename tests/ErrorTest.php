<?php

declare(strict_types=1);

namespace cavo789;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Classes\Error as Error;
use \PHPUnit\Framework\TestCase;

final class ErrorTest extends TestCase
{
	public function testShowError(): void
	{
		/*
		// Instantiate the error handler
		$error = Error::getInstance("<h1>Houston we' ve a problem</h1>" . PHP_EOL .
			'<h2>Error {{ error_code }} - {{ error_title }}</h2>' . PHP_EOL .
			'<div>{{ error_message }}</div>' . PHP_EOL .
			'<hr/>' . PHP_EOL .
			'<div>Please email us</div>');

		// And throw an exception so our handler will be called
		throw new \RuntimeException('Action not supported');
		*/
	}
}
