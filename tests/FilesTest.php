<?php

declare(strict_types=1);

namespace cavo789;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\Files as Files;
use \PHPUnit\Framework\TestCase;

final class FilesTest extends TestCase
{
	public function testFolderExists(): void
	{
		$folder = __DIR__ . '/testFolder';
		Files::makeFolder($folder, true);

		$this->assertDirectoryExists($folder);
	}

	public function testSanitize(): void
	{
		$folder = 'folder/subfolder/\'clean",/';
		// Remove bad characters in folder name
		$this->assertTrue('folder/subfolder/clean/' == Files::sanitize($folder));
	}
}
