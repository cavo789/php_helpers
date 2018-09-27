<?php

/**
 * A few generic functions for the tests scripts
 * Intern; used only for the xxxTest.php scripts
 */

namespace tests\Helpers;

class Utilities
{
	public static function out(
		string $sMessage,
		bool $isTitle = false,
		bool $bReturn = false
	) : string {
		$sReturn = '';
		if ($isTitle) {
			// For readability, add a few empty lines
			for ($i = 0; $i < 5; $i++) {
				$sReturn .= "\r\n";
			}

			$sMessage = '= ' . $sMessage . ' =';

			$sReturn .= str_repeat('=', strlen($sMessage)) . PHP_EOL;
		}

		$sReturn .= $sMessage . PHP_EOL;

		if ($isTitle) {
			$sReturn .= str_repeat('=', strlen($sMessage)) . PHP_EOL . PHP_EOL;
		}

		if (!$bReturn) {
			echo $sReturn;

			return '';
		} else {
			return $sReturn;
		}
	}
}
