<?php

declare(strict_types=1);

namespace cavo789;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Helpers/Utilities.php';

use \cavo789\Helpers\Strings as Strings;
use \PHPUnit\Framework\TestCase;

final class StringsTest extends TestCase
{
	public function teststartsWithcavo(): void
	{
		$this->assertTrue(Strings::startsWith('cavo789', 'cavo'));
		$this->assertFalse(Strings::startsWith('ca_vo789', 'cavo'));
		$this->assertFalse(Strings::startsWith('cavo789', 'ca-vo'));
	}

	public function testendsWithcavo(): void
	{
		$this->assertTrue(Strings::endsWith('cavo789', '789'));
		$this->assertFalse(Strings::endsWith('cavo789', 'zzz'));
	}

	public function testcleansing() : void
	{
		// Double quotes are removed
		$value = '"test_/?"';
		$this->assertTrue('test_/?' == Strings::cleansing($value));
	}
}
