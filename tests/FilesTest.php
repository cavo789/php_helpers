<?php

declare(strict_types=1);

namespace cavo789;

// Autoload files using Composer autoload
require_once dirname(__DIR__) . '/vendor/autoload.php';

use \cavo789\Helpers\Files as Files;
use \PHPUnit\Framework\TestCase;

final class FilesTest extends TestCase
{
    /**
     * Test makeFolder
     *
     * @return void
     */
    public function testFolderExists(): void
    {
        $folder = __DIR__ . '/testFolder';
        Files::makeFolder($folder, true);

        $this->assertDirectoryExists($folder);

        // Verify the presence of the .htaccess for denying access from URL
        $content = file_get_contents($folder . '/.htaccess');
        $this->assertTrue('deny from all' == $content);
    }

    /**
     * Test exists
     *
     * @return void
     */
    public function testExists() : void
    {
        $this->assertTrue(Files::exists(__FILE__));
    }

    /**
     * Test sanitize
     *
     * @return void
     */
    public function testSanitize(): void
    {
        $folder = 'folder/subfolder/\'clean",/';
        // Remove bad characters in folder name
        $this->assertTrue('folder/subfolder/clean/' == Files::sanitize($folder));
    }
}
