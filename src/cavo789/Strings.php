<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * Strings generic helper
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789;

class Strings
{
	/**
	 * Confirm or not if a string is starts with ...
	 *
	 * 	startsWith('Laravel', 'Lara') ==> true
	 *
	 * @link https://stackoverflow.com/a/834355/1065340

	 * @param  string  $string The string
	 * @param  string  $prefix The prefix to search
	 * @return boolean True when the string is ending with that prefix
	 */
	public static function startsWith(string $string, string $prefix) : bool
	{
		$length = strlen($prefix);

		return boolval(substr($string, 0, $length) === $prefix);
	}

	/**
	 * Confirm or not if a string is ending with ...
	 *
	 * 	endsWith('Laravel', 'vel') ==> true
	 *
	 * @link https://stackoverflow.com/a/834355/1065340

	 * @param  string  $string The string
	 * @param  string  $suffix The suffix to search
	 * @return boolean True when the string is ending with that suffix
	 */
	public static function endsWith(string $string, string $suffix): bool
	{
		$length = strlen($suffix);

		if ($length == 0) {
			return true;
		}

		return boolval(substr($string, -$length) === $suffix);
	}

	/**
	 * Make a few cleaning so we can have a proper value
	 *
	 * @param  string $value
	 * @return string
	 */
	public static function cleansing(string $value) : string
	{
		// Remove space, double and single quotes at the start/end
		$value = trim(trim($value, '" '), "'");

		// No carriage return, linefeed or tab allowed
		$value = preg_replace('~\t\r\n~', '', $value);

		// Remove multiple spaces and keep only one
		// "    to much      space" and keep "to much space"
		// @https://stackoverflow.com/q/6394416/1065340
		$value = preg_replace('~\s\s+~', ' ', $value);

		return $value;
	}
}
