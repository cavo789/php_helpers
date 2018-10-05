<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 *
 * Description
 * Make it easier to work with html templates
 */

namespace cavo789\Helpers;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

use \cavo789\Classes\App as App;
use \cavo789\Helpers\Files as Files;
use \cavo789\Helpers\HTML as HTML;

class Template
{
	// Folder where .html templates can be found
	private $folder = '';

	// Define the output mode (can be html, raw, ...)
	private $mode = 'html';
	private $arrSupportedMode = ['html', 'raw'];

	/**
	 * Constructor
	 *
	 * @throws \InvalidArgumentException When the folder name if mentioned and doesn't exists on disk
	 *
	 * @param string $folder
	 */
	public function __construct(string $mode = 'html', string $folder = '')
	{
		self::setMode($mode);

		if (trim($folder) !== '') {
			$this->folder = rtrim(Files::sanitize($folder), DS);

			if (!Files::exists($this->folder)) {
				$this->folder = rtrim(__DIR__, DS) . DS . $this->folder;

				if (!Files::exists($this->folder)) {
					throw new \InvalidArgumentException(sprintf('The folder %s doesn\'t exists', $this->folder));
				}
			}
		}

		$this->folder .= DS;
	}

	/**
	 * Define the mode (f.i. "html" or "raw")
	 *
	 * @throws \InvalidArgumentException When the mode isn't supported
	 *
	 * @param  string $mode
	 * @return void
	 */
	public function setMode(string $mode = 'html')
	{
		$this->mode = trim($mode);
		if (!in_array($this->mode, $this->arrSupportedMode)) {
			throw new \InvalidArgumentException(sprintf('The %s mode isn\'t supported', $this->mode));
		}
	}

	/**
	 * Return the full name to a template
	 *
	 * @throws \InvalidArgumentException when $name is not found i.e. the file didn't exists
	 *
	 * @param  string $name For instance "index"
	 * @return string
	 */
	private function getTemplate(string $name) : string
	{
		$name = Files::sanitize(trim($name));

		$name = $this->folder . $name . '.html';

		if (!Files::exists($name)) {
			throw new \InvalidArgumentException('File ' . str_replace('/', DS, $name) . ' is missing');
		}

		return $name;
	}

	/**
	 * To make things easier to manage, a template can include files
	 * (like in Laravel)
	 * For instance /Templates/interface.html contains a line like
	 * 		{{ include('Partials/interface_options') }}
	 * So, search for such directives and load files.
	 *
	 * This function will use a do ... while because an included file can
	 * also have inclusions
	 *
	 * @param  string $html
	 * @return string
	 */
	private function processInclusions(string $html) : string
	{
		$pattern = "\{\{ include\(\'([^']*)\'\) \}\}";

		$matches = [];
		preg_match_all('~' . $pattern . '~', $html, $matches);

		do {
			if ($matches[0] !== []) {
				list($tags, $files) = $matches;
				for ($i = 0; $i < count($files); $i++) {
					$tmp = self::getTemplate($files[$i]);
					$html = str_replace($tags[$i], file_get_contents($tmp), $html);
				}
			}

			preg_match_all('~' . $pattern . '~', $html, $matches);
		} while ($matches[0] !== []);

		return $html;
	}

	/**
	 * The HTML template can contains conditional blocks like:
	 *
	 * 		<!-- @if_html_start-->
	 *			<link rel="stylesheet" href="assets/css/interface.css">
	 *			<link rel="stylesheet" href="assets/css/AdminLTE.min.css">
	 *			<link rel="stylesheet" href="assets/css/skins/skin-black.min.css">
	 *			<!-- @if_html_end-->
	 *
	 * 		<!-- @if_raw_start-->
	 * 		<h1>This is our RAW mode</h1>
	 * 		<!-- @if_raw_end-->
	 *
	 * One line with <!-- @if_html_start-->, a block and a <!-- @if_html_end-->
	 * final line.
	 * This means that everything between these lines are only needed when the
	 * output mode is the one requested (HTML here).
	 *
	 * This can be for several mode like: html, raw, ...
	 *
	 * So, if the mode isn't that one, remove the entire block
	 *
	 * @param  string $html The HTML template
	 * @return string The same HTML but without these conditional blocks
	 */
	private function removeConditionalModeBlocks(string $html) : string
	{
		// Get supported mode regex list (so get "html|raw") f.i.
		$supported = implode('|', $this->arrSupportedMode);

		$pattern =
			'<\!\-\- \@if_((' . $supported . '))_start' .
			'.*?' .
			'\@if_(' . $supported . ')_end\-\-\>';

		// Verify if we've conditional blocks
		if (preg_match_all('~' . $pattern . '~ms', $html, $matches)) {
			$arrFound = [];

			// Yes, for this moment, just retrieve all mode (html, raw, ...)
			// used in conditional blocks so if the template as blocks like
			// 	<!-- @if_html_start-->...<!-- @if_html_end-->
			// 	<!-- @if_raw_start-->...<!-- @if_raw_end-->
			// then $arrFound will contains ['html', 'raw']
			foreach ($matches[1] as $match) {
				if (!in_array($match, $arrFound)) {
					$arrFound[] = $match;
				}
			}

			// If we the current mode if f.i. "html" and we've in our
			// $arrFound table "HTML" and "RAW", so we need here to remove
			// the html entry (since we want to keep conditional HTML blocks)
			// and only want to remove "RAW" blocks
			if (array_search($this->mode, $arrFound) !== false) {
				// Ok, we're ready for removing any blocks not related
				// to the current mode (so, if $this->mode is "html", then
				// we can remove all others blocks for mode "raw" f.i.
				// Remove our current mode ("html") from $arrFound
				unset($arrFound[array_search($this->mode, $arrFound)]);
			}

			// $arrFound contains now all conditionals blocks that we
			// need to remove
			if (count($arrFound) > 0) {
				// We've still conditional blocks so remove them

				foreach ($arrFound as $mode) {
					$pattern =
						'<\!\-\- \@if_' . $mode . '_start' .
						'.*?' .
						'\@if_' . $mode . '_end\-\-\>';

					$html = preg_replace('~' . $pattern . '~is', '', $html);
				}
			}
		}

		return $html;
	}

	/**
	 * The HTML template can contains conditional blocks like:
	 *
	 * 		<!-- @if_full_start-->
	 *			<link rel="stylesheet" href="assets/css/interface.css">
	 *			<link rel="stylesheet" href="assets/css/AdminLTE.min.css">
	 *			<link rel="stylesheet" href="assets/css/skins/skin-black.min.css">
	 *			<!-- @if_full_end-->
	 *
	 * 		<!-- @if_ajax_start-->
	 * 		<h1>This page has been called by Ajax</h1>
	 * 		<!-- @if_ajax_end-->
	 *
	 * "full" when the page has been loaded with a direct URL and
	 * "ajax" when called through an Ajax request.
	 *
	 * In "full" mode, html meta, favicon, css, ... can be present in
	 * the page but this isn't needed and should be avoided when the call
	 * was made with ajax so, for instance, we can have in our template:
	 *
	 * 	<!-- @if_full_start-->
	 *		<link rel="stylesheet" href="assets/css/interface.css">
	 *		<link rel="stylesheet" href="assets/css/AdminLTE.min.css">
	 *		<link rel="stylesheet" href="assets/css/skins/skin-black.min.css">
	 *		<!-- @if_full_end-->
	 *
	 * to indicate that these lines should be removed when called by Ajax
	 *
	 * @param  string $html The HTML template
	 * @return string The same HTML but without these conditional blocks
	 */
	private function removeConditionalRequestBlocks(string $html) : string
	{
		// Detect if the call has been made with an Ajax request or not
		$isAjax = HTML::isAjaxRequest();

		// When Ajax mode then we'll remove "if_full_xxx" blocks or
		// the opposite when normal requests (not Ajax)
		$remove = $isAjax ? 'full' : 'ajax';

		$pattern =
			'<\!\-\- \@if_' . $remove . '_start' .
			'.*?' .
			'\@if_' . $remove . '_end\-\-\>';

		$html = preg_replace('~' . $pattern . '~is', '', $html);

		return $html;
	}

	/**
	 * Return the HTML of the template
	 *
	 * @param  string $template
	 * @param  array  $arrVariables
	 * @return string
	 */
	public function show(
		string $template = 'interface',
		array $arrVariables = []
	) : string {
		// Retrieve the full name of the file
		$file = self::getTemplate($template);

		// Get the file's content on disk
		$html = file_get_contents($file);

		// Process all inclusion directives
		$html = self::processInclusions($html);

		// Remove conditional blocks based on the display mode (html, raw, ...)
		$html = self::removeConditionalModeBlocks($html);

		// Remove Ajax/Full blocks based on how the request was mode (Ajax or not)
		$html = self::removeConditionalRequestBlocks($html);

		// Remove any <!-- html comments -->
		$html = HTML::removeHTMLComments($html);

		$url = HTML::getCurrentURL(true);
		$html = str_replace('{{ url }}', $url, $html);

		$app = App::getInstance();
		$html = str_replace('{{ debug }}', strval($app->getDebugMode() ? 1 : 0), $html);

		/**
		 * The HTML template can contains user tags like
		 * 	{{ download_html }}
		 * 	{{ download_pdf }}
		 *		{{ new_window_html }}
		 *		{{ my_own_tag }}
		 *
		 * Get the list of key-value pair in the $arrVariables array and if one
		 * key is mentioned in the file replace the tag by its value
		 */
		foreach ($arrVariables as $key => $value) {
			if (strpos($html, '{{ ' . $key . ' }}') !== false) {
				$html = str_replace('{{ ' . $key . ' }}', $value, $html);
			}
		}

		return trim($html, " \n");
	}
}
