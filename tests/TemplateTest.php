<?php

declare(strict_types=1);

namespace cavo789\tests;

use \cavo789\Helpers\Template as Template;
use \PHPUnit\Framework\TestCase;

final class TemplateTest extends TestCase
{
    public function testShowTemplate()
    {
        // Process the HTML and replace variables and return his HTML
        $temp = new Template('html', __DIR__ . DIRECTORY_SEPARATOR . 'Templates');

        $arrVariables = [
            'title' => 'Test Template',
            'content' => 'Hello, this is my nice content',
            'css' => '<link rel="stylesheet" href="style.css" media="screen"/>',
            'js' => '<script src="js/jquery.js"></script>'
        ];

        // When using "html" mode, we expect to have CSS and JS
        // No carriage return, no tabs / spaces between tags since they're not useful
        $expected =
            '<!DOCTYPE html>' .
            '<html>' .
            '<head>' .
            '<meta charset="utf-8">' .
            '<meta http-equiv="X-UA-Compatible" content="IE=edge">' .
            '<title>Test Template</title>' .
            '<link rel="stylesheet" href="http:/assets/css/interface.css">' .
            '</head>' .
            '<body class="hold-transition login-page">' .
            'Hello, this is my nice content' .
            '</body>' .
            '</html>';

        $this->assertTrue($expected == $temp->show('login', $arrVariables));

        // But when using "raw" mode, we didn't expect to have CSS and JS
        // These lines should disappear
        $temp->setMode('raw');

        $expected =
            '<!DOCTYPE html>' .
            '<html>' .
            '<body class="hold-transition login-page">' .
            'Hello, this is my nice content' .
            '</body>' .
            '</html>';

        $this->assertTrue($expected == $temp->show('login', $arrVariables));
    }
}
