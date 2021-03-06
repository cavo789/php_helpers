<?php

declare(strict_types = 1);

/**
 * Christophe Avonture (most coming from Laravel framework)
 * Written date : 2018-09-13.
 *
 * Description
 * Generic helper functions for working with Arrays
 */

namespace cavo789\Helpers;

class ArrayHelper
{
    /**
     * Simple function that will convert array with items into a string.
     *
     * @param array  $arr
     * @param string $function  User function that will be called for each item
     * @param string $separator Separator to put between two items (f.i. PHP_EOL or ";" or "-" ...)
     *
     * ### Examples
     *
     * Convert an array into a string; each item delimited by a EOL char;
     * the code below will display three lines; one item by line:
     *
     * ```php
     * $arr = ['style.css', 'interface.css', 'demo.css']
     * echo array2string($arr, '', PHP_EOL);
     * ```
     *
     * Same but delimited by a ";"
     * the code below will display one line; items like "style.css;interface.css;demo.css"
     *
     * ```php
     * $arr = ['style.css', 'interface.css', 'demo.css']
     * echo array2string($arr, '', ';');
     * ```
     *
     * @return string
     */
    public static function array2string(
        array $arr,
        string $function = '',
        string $separator = PHP_EOL
    ): string {
        $return = '';

        foreach ($arr as $value) {
            if (!empty($function)) {
                $value = call_user_func($function, $value);
            }

            $return .= $value . $separator;
        }

        return rtrim($return, $separator);
    }

    /**
     * Convert an associative array into a string.
     *
     * @param array  $arr
     * @param string $separator Separator to put between two items (f.i. PHP_EOL or ";" or "-" ...)
     *
     * ### Examples
     *
     * Convert an array into a string; output key and value.
     * The following example will return "name='Avonture',firstname='Christophe'"
     *
     * ```php
     * $arr = ["name"=>"Avonture","firstname"=>"Christophe"];
     * echo array2string($arr, ',');
     * ```
     *
     * @return string
     */
    public static function associativearray2string(
        array $arr,
        string $delimiter = ';'
    ): string {
        $return = '';

        if (count($arr) > 0) {
            $return = implode($delimiter, array_map(
                function ($v, $k) {
                    return sprintf("%s='%s'", $k, $v);
                },
                $arr,
                array_keys($arr)
            ));
        }

        return $return;
    }

    /**
     * Convert an associative array (for instance the result of a
     * SQL SELECT STATEMENT) into a CSV string.
     *
     * @param array  $arr       The array with the records
     * @param string $delimiter The delimiter to use (default is ";")
     *
     * @return string The CSV string
     */
    public static function array2csv(array $arr, string $delimiter = ';'): string
    {
        $sCSV = '';

        // Empty ? Nothing to do
        if (0 == count($arr)) {
            return $sCSV;
        }

        // No spaces f.i. before or after
        $delimiter = trim($delimiter);

        // Can't be empty
        if ('' == $delimiter) {
            $delimiter = ';';
        }

        // Get the field name
        foreach ($arr[0] as $key => $value) {
            $sCSV .= $key . $delimiter;
        }
        $sCSV = trim($sCSV, $delimiter) . PHP_EOL;

        // Now process the array and export all rows into a CSV row
        for ($i = 0; $i < count($arr); $i++) {
            // @phan-suppress-next-line PhanUnusedVariable
            foreach ($arr[$i] as $key => $value) {
                $sCSV .= $value . $delimiter;
            }
            $sCSV = trim($sCSV, $delimiter) . PHP_EOL;
        }

        return trim($sCSV, PHP_EOL);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
     *
     * See also arraySet() for updating the value of a key in an associative array
     *
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function arrayGet(array $array, string $key, $default = null)
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

        if (false === strpos($key, '.')) {
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
     * Set an array item to a given value using "dot" notation.
     * If no key is given to the method, the entire array will be replaced.
     *
     * See also arrayGet() for getting the value of a key in an associative array
     *
     * @see https://github.com/padosoft/support/blob/master/src/array.php#L130
     *
     * @param array  $array The array
     * @param string $key   The key to search, in "dot" notation
     * @param mixed  $value The new value
     *
     * @return array
     */
    public static function arraySet(array &$array, string $key, $value): array
    {
        if (is_null($key)) {
            $array[] = $value;

            return $array;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Transpose a two-dimensional array.
     *
     * ### Example
     *
     * We've an array by user and, for each user, we have a question and
     * the answer.
     *
     * $in = [
     *  'User1' => [
     *      'Question1' => 'Answer User1 - Q1',
     *      'Question2' => 'Answer User1 - Q2',
     *      'Question3' => 'Answer User1 - Q3'
     *  ],
     *  'User2' => [
     *      'Question1' => 'Answer User2 - Q1',
     *      'Question2' => 'Answer User2 - Q2',
     *      'Question3' => 'Answer User2 - Q3'
     *  ],
     *  'User3' => [
     *      'Question1' => 'Answer User3 - Q1',
     *      'Question2' => 'Answer User3 - Q2',
     *      'Question3' => 'Answer User3 - Q3'
     *  ]
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
     *      'Question1' => [
     *          'User1' => 'Answer User1 - Q1',
     *          'User2' => 'Answer User2 - Q1',
     *          'User3' => 'Answer User3 - Q1'
     *      ],
     *      'Question2' => [
     *          'User1' => 'Answer User1 - Q2',
     *          'User2' => 'Answer User2 - Q2',
     *          'User3' => 'Answer User3 - Q2'
     *      ],
     *      'Question3' => [
     *          'User1' => 'Answer User1 - Q3',
     *          'User2' => 'Answer User2 - Q3',
     *          'User3' => 'Answer User3 - Q3'
     *      ]
     *  ]
     *
     *
     * @see https://stackoverflow.com/questions/797251/transposing-multidimensional-arrays-in-php/797268#797268
     *
     * @param array $arr
     *
     * @return array
     */
    public static function transpose(array $arr): array
    {
        $out = [];
        foreach ($arr as $key => $subarr) {
            foreach ($subarr as $subkey => $subvalue) {
                $out[$subkey][$key] = $subvalue;
            }
        }

        return $out;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
     *
     * @param array  $array
     * @param string $key
     *
     * @return bool
     */
    private static function exists(array $array, string $key): bool
    {
        if ($array instanceof \ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @filesource Laravel - vendor/laravel/framework/src/Illuminate/Support/Arr.php
     *
     * @param mixed $value
     *
     * @return bool
     */
    private static function accessible($value): bool
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }
}
