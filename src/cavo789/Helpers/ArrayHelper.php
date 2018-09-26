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
	 * @param  array  $function User function that will be called for each item
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
	 * Determine if the given key exists in the provided array.
	 * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
	 *
	 * @param  [type] $array
	 * @param  [type] $key
	 * @return void
	 */
	private static function exists($array, $key)
	{
		if ($array instanceof ArrayAccess) {
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
	private static function accessible($value)
	{
		return is_array($value) || $value instanceof ArrayAccess;
	}

	/**
	 * Get an item from an array using "dot" notation.
	 * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
	 *
	 * @param  \ArrayAccess|array $array
	 * @param  string             $key
	 * @param  mixed              $default
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
	 * @return void
	 */
	public static function transpose(array $arr)
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
