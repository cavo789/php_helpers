<?php

declare(strict_types=1);

namespace cavo789\tests;

use \cavo789\Helpers\HTML as HTML;
use \PHPUnit\Framework\TestCase;

final class HTMLTest extends TestCase
{
    /**
     * Test makeLink().
     *
     * @return void
     */
    public function testMakeLink()
    {
        // True for adding rel="noopener noreferrer"
        $result = HTML::makeLink('www.google.be', 'Google', ['class' => 'link'], true);
        $expected = '<a href="www.google.be" class="link" rel="noopener noreferrer">Google</a>';
        $this->assertSame($result, $expected);

        // False for not adding rel="noopener noreferrer"
        $result = HTML::makeLink('www.google.be', 'Google', ['class' => 'link'], false);
        $expected = '<a href="www.google.be" class="link">Google</a>';
        $this->assertSame($result, $expected);

        // Lot of extras
        $result = HTML::makeLink('www.google.be', 'Google', ['class' => 'link', 'data-key' => 'a_key'], false);
        $expected = '<a href="www.google.be" class="link" data-key="a_key">Google</a>';
        $this->assertSame($result, $expected);

        // Remove target="_blank"
        $result = HTML::makeLink(
            'www.google.be',
            'Google',
            ['target' => '_blank'],
            false,
            true
        );
        $expected = '<a href="www.google.be">Google</a>';
        $this->assertSame($result, $expected);
    }

    /**
     * Test addCSSTag().
     *
     * @return void
     */
    public function testAddCSSTag()
    {
        $value = HTML::addCSSTag('style.css');
        $expected = '<link rel="stylesheet" href="style.css" media="screen"/>';
        $this->assertSame($value, $expected);
    }

    /**
     * Test AddJSTag.
     *
     * @return void
     */
    public function testAddJSTag()
    {
        $value = HTML::addJSTag('script.js');
        $expected = '<script type="text/javascript" src="script.js"></script>';
        $this->assertSame($value, $expected);
    }

    /**
     * Test RemoveHTMLComments.
     *
     * @return void
     */
    public function testRemoveHTMLComments()
    {
        $value = '<!-- a comment --><h1>Test</h1><!-- something else -->';
        $value = HTML::removeHTMLComments($value);
        $expected = '<h1>Test</h1>';
        $this->assertSame($value, $expected);
    }

    /**
     * Test csv2table.
     *
     * @return void
     */
    public function testCsv2table()
    {
        $csv = "col1;col2;col3\nrow1-1;row1-2;row1-3;\nrow2-1;row2-2;row2-3";

        // Simple
        $value = HTML::csv2table($csv);
        $expected =
            '<table><thead><tr><th>col1</th><th>col2</th><th>col3</th></tr></thead>' .
            '<tbody><tr><td>row1-1</td><td>row1-2</td><td>row1-3</td><td></td></tr>' .
            '<tr><td>row2-1</td><td>row2-2</td><td>row2-3</td></tr></tbody></table>';
        $this->assertSame($value, $expected);

        // Enhanced
        $value = HTML::csv2table($csv, ['enhanced' => true]);
        $expected = '<table><thead><tr><th>col1</th><th>col2</th><th>col3</th></tr>' .
            '</thead><tfoot><tr><th>col1</th><th>col2</th><th>col3</th></tr></tfoot>' .
            '<tbody><tr><td>row1-1</td><td>row1-2</td><td>row1-3</td><td></td></tr>' .
            '<tr><td>row2-1</td><td>row2-2</td><td>row2-3</td></tr></tbody></table>';
        $this->assertSame($value, $expected);

        // Enhanced, ID, class, style
        $value = HTML::csv2table(
            $csv,
            [
                'enhanced' => true,
                'id' => 'tblTest',
                'class' => 'table table-hover table-bordered table-striped dataTable',
                'style' => 'background-color:red;font-size:3em;',
                'role' => 'grid',
                'data-attr' => 'MyAwesomeAttribute'
            ]
        );

        $expected = '<table id="tblTest" ' .
            'class="table table-hover table-bordered table-striped dataTable" ' .
            'style="background-color:red;font-size:3em;" role="grid" ' .
            'data-attr="MyAwesomeAttribute">' .
            '<thead><tr><th>col1</th><th>col2</th><th>col3</th></tr></thead>' .
            '<tfoot><tr><th>col1</th><th>col2</th><th>col3</th></tr></tfoot>' .
            '<tbody><tr><td>row1-1</td><td>row1-2</td><td>row1-3</td><td></td></tr>' .
            '<tr><td>row2-1</td><td>row2-2</td><td>row2-3</td></tr></tbody></table>';
        $this->assertSame($value, $expected);
    }

    /**
     * Test isAjaxRequest.
     *
     * @return void
     */
    public function testIsAjaxRequest()
    {
        $this->assertFalse(HTML::isAjaxRequest());
    }

    /**
     * Test compress.
     *
     * @return void
     */
    public function testCompress()
    {
        $value =
            '<section>' .
            '		<div class="container">		' .
            '					<div class="row">' .
            '<!-- CONTENT --><p>Main content</p></div></div></section>';

        $value = HTML::compress($value);

        $expected = '<section><div class="container"><div class="row">' .
            '<!-- CONTENT --><p>Main content</p></div></div></section>';

        $this->assertSame($expected, $value);
    }
}
