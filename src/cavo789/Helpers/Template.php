<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * Make it easier to work with html templates
 */

declare(strict_types=1);

namespace cavo789\Helpers;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

use cavo789\Classes\App as App;
use cavo789\Helpers\Files as Files;
use cavo789\Helpers\HTML as HTML;

class Template
{
	// Folder where .html templates can be found
	private $folder = '';

	// Define the output mode (can be html, raw, ...)
	private $mode = 'html';
	private $arrSupportedMode = ['html', 'raw'];

	/**
	 * Undocumented function
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
	 * Undocumented function
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
		$file = self::getTemplate($template);

		$html = file_get_contents($file);

		$html = self::processInclusions($html);

		// The template can contains blocks like:
		//
		//		<!-- @if_html_start-->
		//		<link rel="stylesheet" href="assets/css/interface.css">
		//		<link rel="stylesheet" href="assets/css/AdminLTE.min.css">
		//		<link rel="stylesheet" href="assets/css/skins/skin-black.min.css">
		//		<!-- @if_html_end-->
		//
		// One line with <!-- @if_html_start-->, a bloc and a <!-- @if_html_end-->
		// final line.
		//
		// This means that everything between these lines are only needed when the
		// output mode is the one requested (HTML here).
		// So, if the mode isn't that one, remove the entire block
		if ($this->mode !== 'html') {
			$pattern = '<\!\-\- \@if_html_start.*?\@if_html_end\-\-\>';
			$html = preg_replace('~' . $pattern . '~is', '', $html);
		}

		// Remove any <!-- html comments -->
		$html = HTML::removeHTMLComments($html);

		$url = HTML::getCurrentURL(true);
		$html = str_replace('{{ url }}', $url, $html);

		$app = App::getInstance();
		$html = str_replace('{{ debug }}', $app->getDebugMode() ? 1 : 0, $html);

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

		return $html;
	}
}
