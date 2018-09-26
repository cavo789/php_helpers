<?php

/**
 * A few generic functions for the tests scripts
 * Intern; used only for the xxxTest.php scripts
 */

namespace tests\Helpers;

class Utilities
{
	public static function out(string $str, bool $isTitle = false)
	{
		if ($isTitle) {
			// For readability, add a few empty lines
			for ($i = 0; $i < 5; $i++) {
				echo "\r\n";
			}

			$str = '= ' . $str . ' =';

			echo str_repeat('=', strlen($str)) . PHP_EOL;
		}

		echo $str . PHP_EOL;

		if ($isTitle) {
			echo  str_repeat('=', strlen($str)) . PHP_EOL . PHP_EOL;
		}
	}
}
