<?php

declare(strict_types=1);

namespace cavo789\tests;

use \PHPUnit\Framework\TestCase;
use \cavo789\Helpers\ArrayHelper as ArrayHelper;

final class ArrayHelperTest extends TestCase
{
    /**
     * Test array2string function.
     *
     * @return void
     */
    public function testarray2string()
    {
        $arr = ['style.css', 'interface.css', 'demo.css'];
        $result = ArrayHelper::array2string($arr);

        // Just each item on a new line
        $expected = 'style.css' . PHP_EOL . 'interface.css' . PHP_EOL . 'demo.css';

        $this->assertTrue($expected == $result);

        // addCSSTag will add the stylesheet tag if not already mentionned
        //  <link rel="stylesheet" href="xxxx" media="screen"/>
        $result = ArrayHelper::array2string($arr, 'cavo789\Helpers\HTML::addCSSTag');

        $expected =
            '<link rel="stylesheet" href="style.css" media="screen"/>' . PHP_EOL .
            '<link rel="stylesheet" href="interface.css" media="screen"/>' . PHP_EOL .
            '<link rel="stylesheet" href="demo.css" media="screen"/>';

        $this->assertTrue($expected == $result);
    }

    /**
     * Test array2csv function.
     *
     * @return void
     */
    public function testarray2csv()
    {
        $arr = [];

        $arr[] = ['FieldName' => 'FirstName', 'Value' => 'Christophe'];
        $arr[] = ['FieldName' => 'FirstName', 'Value' => 'Marc'];
        $arr[] = ['FieldName' => 'FirstName', 'Value' => 'Frédérique'];
        $value = ArrayHelper::array2csv($arr);

        $expected =
            'FieldName;Value' . PHP_EOL .
            'FirstName;Christophe' . PHP_EOL .
            'FirstName;Marc' . PHP_EOL .
            'FirstName;Frédérique';

        $this->assertTrue($value == $expected);

        // Use another delimiter
        $value = ArrayHelper::array2csv($arr, '@');

        $expected =
            'FieldName@Value' . PHP_EOL .
            'FirstName@Christophe' . PHP_EOL .
            'FirstName@Marc' . PHP_EOL .
            'FirstName@Frédérique';

        $this->assertTrue($value == $expected);
    }

    /**
     * Test arrayGet function
     * Get the value of a key in an associative array using the "dot" notation.
     *
     * @return void
     */
    public function testarrayGet()
    {
        // Create an associative array
        $json = '{ "cdn" : { "enabled" : "1", "css" : [ "style.css", "interface.css", "demo.css" ] } }';
        $arr = json_decode($json, true);

        // Use dot notation to retrieve a key; get "cdn.enabled"
        // Retrieve a boolean
        $value = boolval(ArrayHelper::arrayGet($arr, 'cdn.enabled'));
        $this->assertTrue($value);

        // Retrieve an array
        $arr = ArrayHelper::arrayGet($arr, 'cdn.css');
        $expected = ['style.css', 'interface.css', 'demo.css'];
        $this->assertTrue($arr == $expected);
    }

    /**
     * Test arraySet function
     * Update the value of a key in an associative array using the "dot" notation.
     *
     * @return void
     */
    public function testarraySet()
    {
        // Create an associative array
        $json = '{ "cdn" : { "enabled" : "1", "css" : [ "style.css", "interface.css", "demo.css" ] } }';
        $arr = json_decode($json, true);

        // Use dot notation to retrieve a key; get "cdn.enabled"
        // Retrieve a boolean
        $value = boolval(ArrayHelper::arrayGet($arr, 'cdn.enabled'));
        $this->assertTrue($value);

        // Change the value, set it to false
        ArrayHelper::arraySet($arr, 'cdn.enabled', 0);
        $value = boolval(ArrayHelper::arrayGet($arr, 'cdn.enabled'));
        $this->assertFalse($value);
    }

    /**
     * Test transpose function.
     *
     * @return void
     */
    public function testtranspose()
    {
        // Define a two dimensional array
        $arr = [];
        $arr['User1']['Question1'] = 'Answer User1 - Q1';
        $arr['User2']['Question1'] = 'Answer User2 - Q1';
        $arr['User3']['Question1'] = 'Answer User3 - Q1';
        $arr['User1']['Question2'] = 'Answer User1 - Q2';
        $arr['User2']['Question2'] = 'Answer User2 - Q2';
        $arr['User3']['Question2'] = 'Answer User3 - Q2';
        $arr['User1']['Question3'] = 'Answer User1 - Q3';
        $arr['User2']['Question3'] = 'Answer User2 - Q3';
        $arr['User3']['Question3'] = 'Answer User3 - Q3';

        // Expected: the same table but transposed
        $expected = [];
        $expected['Question1']['User1'] = 'Answer User1 - Q1';
        $expected['Question1']['User2'] = 'Answer User2 - Q1';
        $expected['Question1']['User3'] = 'Answer User3 - Q1';
        $expected['Question2']['User1'] = 'Answer User1 - Q2';
        $expected['Question2']['User2'] = 'Answer User2 - Q2';
        $expected['Question2']['User3'] = 'Answer User3 - Q2';
        $expected['Question3']['User1'] = 'Answer User1 - Q3';
        $expected['Question3']['User2'] = 'Answer User2 - Q3';
        $expected['Question3']['User3'] = 'Answer User3 - Q3';

        $this->assertTrue($expected == ArrayHelper::transpose($arr));
    }
}
