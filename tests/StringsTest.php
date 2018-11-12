<?php

declare(strict_types = 1);

namespace cavo789\tests;

use \cavo789\Helpers\Strings as Strings;
use \PHPUnit\Framework\TestCase;

final class StringsTest extends TestCase
{
    public function teststartsWithcavo()
    {
        $this->assertTrue(Strings::startsWith('cavo789', 'cavo'));
        $this->assertFalse(Strings::startsWith('ca_vo789', 'cavo'));
        $this->assertFalse(Strings::startsWith('cavo789', 'ca-vo'));
    }

    public function testendsWithcavo()
    {
        $this->assertTrue(Strings::endsWith('cavo789', '789'));
        $this->assertFalse(Strings::endsWith('cavo789', 'zzz'));
    }

    public function testcleansing()
    {
        // Double quotes are removed
        $value = '"test_/?"';
        $this->assertTrue('test_/?' == Strings::cleansing($value));
    }

    public function testisBase64()
    {
        // Is a base 64 encoded string
        $this->assertTrue(Strings::isBase64('SSdtIGEgc3RyaW5n'));

        // These are not base64 encoded string; just ASCII values
        $this->assertFalse(Strings::isBase64(''));
        $this->assertFalse(Strings::isBase64("I'm a string"));
    }
}
