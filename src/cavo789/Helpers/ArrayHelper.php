<?php

/**
 * Christophe Avonture (most coming from Laravel framework)
 * Written date : 2018-09-13
 * Last modified:
 *
 * Generic helper functions for working with Arrays
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789\Helpers;

class ArrayHelper
{
	/**
	 * Simple function that will convert array with items into a string
	 *
	 * @param  array  $arr
	 * @param  string $function User function that will be called for each item
	 * @return string
	 */
	public static function array2string(array $arr, string $function = '') : string
	{
		$return = '';

		foreach ($arr as $value) {
			if (!empty($function)) {
				$value = call_user_func($function, $value);
			}

			$return .= $value . PHP_EOL;
		}

		return $return;
	}

	/**
	 * Convert an associative array (for instance the result of a
	 * SQL SELECT STATEMENT) into a CSV string.
	 *
	 * @param  array  $arr       The array with the records
	 * @param  string $delimiter The delimiter to use (default is ";")
	 * @return string The CSV string
	 */
	public static function array2csv(array $arr, string $delimiter = ';') : string
	{
		$sCSV = '';

		// Emtpy ? Nothing to do
		if (count($arr) == 0) {
			return $sCSV;
		}

		// No spaces f.i. before or after
		$delimiter = trim($delimiter);

		// Can't be empty
		if ($delimiter == '') {
			$delimiter = ';';
		}

		// Get the field name
		foreach ($arr[0] as $key => $value) {
			$sCSV .= $key . $delimiter;
		}
		$sCSV = trim($sCSV, $delimiter) . PHP_EOL;

		// Now process the array and export all rows into a CSV row
		for ($i = 0; $i < count($arr); $i++) {
			foreach ($arr[$i] as $key => $value) {
				$sCSV .= $value . $delimiter;
			}
			$sCSV = trim($sCSV, $delimiter) . PHP_EOL;
		}

		return trim($sCSV, PHP_EOL);
	}

	/**
	 * Determine if the given key exists in the provided array.
	 * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @return boolean
	 */
	private static function exists(array $array, string $key) : bool
	{
		if ($array instanceof \ArrayAccess) {
			return $array->offsetExists($key);
		}

		return array_key_exists($key, $array);
	}

	/**
	 * Determine whether the given value is array accessible.
	 * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
	 *
	 * @param  mixed $value
	 * @return bool
	 */
	private static function accessible($value) : bool
	{
		return is_array($value) || $value instanceof \ArrayAccess;
	}

	/**
	 * Get an item from an array using "dot" notation.
	 * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
	 *
	 * @param  array  $array
	 * @param  string $key
	 * @param  mixed  $default
	 * @return mixed
	 */
	public static function array_get(array $array, string $key, $default = null)
	{
		if (!static::accessible($array)) {
			return $default;
		}
		if (is_null($key)) {
			return $array;
		}

		if (static::exists($array, $key)) {
			return $array[$key];
		}

		if (strpos($key, '.') === false) {
			return $array[$key] ?? $default;
		}

		foreach (explode('.', $key) as $segment) {
			if (static::accessible($array) && static::exists($array, $segment)) {
				$array = $array[$segment];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * Transpose a two-dimensional array
	 *
	 * ### Example
	 *
	 * We've an array by user and, for each user, we have a question and
	 * the answer.
	 *
	 * $in = [
	 * 	'User1' => [
	 * 		'Question1' => 'Answer User1 - Q1',
	 * 		'Question2' => 'Answer User1 - Q2',
	 * 		'Question3' => 'Answer User1 - Q3'
	 * 	],
	 * 	'User2' => [
	 * 		'Question1' => 'Answer User2 - Q1',
	 * 		'Question2' => 'Answer User2 - Q2',
	 * 		'Question3' => 'Answer User2 - Q3'
	 * 	],
	 * 	'User3' => [
	 * 		'Question1' => 'Answer User3 - Q1',
	 * 		'Question2' => 'Answer User3 - Q2',
	 * 		'Question3' => 'Answer User3 - Q3'
	 * 	]
	 * ];
	 *
	 * We can transpose the array to have first the question then
	 * the answer given to that question by each user.
	 *
	 * So User->Question->Answer should become Question->User->Answer
	 *
	 * $out = Transpose($in);
	 *
	 * This will give:
	 *
	 * $out = [
	 *		'Question1' => [
	 *			'User1' => 'Answer User1 - Q1',
	 *			'User2' => 'Answer User2 - Q1',
	 *			'User3' => 'Answer User3 - Q1'
	 *		],
	 *		'Question2' => [
	 *			'User1' => 'Answer User1 - Q2',
	 *			'User2' => 'Answer User2 - Q2',
	 *			'User3' => 'Answer User3 - Q2'
	 *		],
	 *		'Question3' => [
	 *			'User1' => 'Answer User1 - Q3',
	 *			'User2' => 'Answer User2 - Q3',
	 *			'User3' => 'Answer User3 - Q3'
	 *		]
	 *	]
	 *
	 *
	 * @link https://stackoverflow.com/questions/797251/transposing-multidimensional-arrays-in-php/797268#797268

	 * @param  array $arr
	 * @return array
	 */
	public static function transpose(array $arr) : array
	{
		$out = [];
		foreach ($arr as $key => $subarr) {
			foreach ($subarr as $subkey => $subvalue) {
				$out[$subkey][$key] = $subvalue;
			}
		}

		return $out;
	}
}
