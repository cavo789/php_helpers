<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * HTML Helper
 * Reusable in other projects
 */

declare(strict_types=1);

namespace cavo789;

class HTML
{
	/**
	 * Create a link ("<a href="...">...</a>)
	 *
	 * @param  string $url               The URL like f.i. www.google.be
	 * @param  string $text              The text like "Click here to ..."
	 * @param  array  $extra             Any extra parameter (rel, target, class, ...)
	 * @param  bool   $secure            When true, the link will be make safe
	 * @param  bool   $removeTargetBlank Remove target=_blank if found
	 * @return string
	 */
	public static function makeLink(
		string $url,
		string $text,
		array $extra = [],
		bool $secure = true,
		bool $removeTargetBlank = true
	) : string {
		// extra is an array like f.i.
		//	Array (
		// 	[id] => download
		//		[class] => download_file
		//		[target] => _blank
		// )

		if ($removeTargetBlank) {
			// Remove target="_blank"
			// @link https://medium.com/@alirak94/how-to-fix-target-blank-a-security-and-performance-issue-in-web-pages-2118eba1ce2f

			$target = trim($extra['target'] ?? 'none');
			if ($target == '_blank') {
				unset($extra['target']);
			}
		}

		if ($secure) {
			// Add a noopener noreferrer attribute for preventing
			// security breaches
			if (!isset($extra['rel'])) {
				$extra['rel'] = '';
			}

			$rel = trim($extra['rel'] ?? 'none');
			if (strpos($rel, 'noopener ') === false) {
				$extra['rel'] .= ' noopener ';
			}
			if (strpos($rel, 'noreferrer') === false) {
				$extra['rel'] .= ' noreferrer';
			}
		}

		// Flatten the array i.e. convert the array as a string like
		//		id ="download" class ="download_file" target ="_blank"
		$flattened = $extra;

		array_walk($flattened, function (&$value, $key) {
			$value = $key . ' ="' . $value . '" ';
		});

		// Make the link
		$link = '<a href="' . $url . '" ' . implode(' ', $flattened) . '>' .
			$text . '</a>';

		return $link;
	}

	/**
	 * Return the current URL
	 *
	 * @return string
	 */
	public static function getCurrentURL() : string
	{
		return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	}

	/**
	 * Add a querystring parameter. When the parameter was already present,
	 * update his value
	 *
	 * @param  array   $params
	 * @param  boolean $addCurrentURL
	 * @return string
	 */
	public static function addURLParameter(
		array $params,
		bool $addCurrentURL = true
	) : string {
		// Retrieve all current parameters and add the new parameter
		// If this parameter was already there, change its value
		// So index.php?mode=html will be replaced by index.php?mode=raw
		$params = array_merge($_GET, $params);
		$queryString = http_build_query($params);

		if ($addCurrentURL) {
			$url = self::getCurrentURL();

			return  $url . '?' . $queryString;
		} else {
			return $queryString;
		}
	}

	/**
	 * Get a value from the querystring (URL)
	 *
	 * @param  string  $name    The name of the variable (f.i. "action")
	 * @param  string  $default A default value (f.i. "showIndex")
	 * @param  string  $filter
	 * @param  integer $source
	 * @return void    (can be a string or a int or ...)
	 */
	public static function getParameter(
		string $name,
		$default = '',
		string $filter = 'string',
		int $source = INPUT_GET
	) {
		// List of filters @https://www.w3schools.com/php/php_ref_filter.asp
		switch ($filter) {
			case 'bool':
				$filter_type = FILTER_VALIDATE_BOOLEAN;
				break;

			case 'int':
				$filter_type = FILTER_VALIDATE_INT;
				break;

			default:
				$filter_type = FILTER_SANITIZE_STRING;
		}

		$value = filter_input($source, $name, $filter_type);

		$value = (empty($value) ? $default : $value);

		if ($filter == 'string') {
			$value = trim($value);
		}

		return $value;
	}

	/**
	 * Get a posted variable's value
	 *
	 * @param  string $name
	 * @param  string $default
	 * @param  string $filter
	 * @return void
	 */
	public static function getFormVariable(
		string $name,
		$default = '',
		string $filter = 'string'
	) {
		$value = self::getParameter($name, $default, $filter, INPUT_POST);

		return self::getParameter($name, $default, $filter, INPUT_POST);
	}

	/**
	 * Force the download of a file
	 *
	 * @throws \InvalidArgumentException When $filename doesn't exists on disk
	 *
	 * @param  string $filename Full name to the file f.i. c:\...\file.csv
	 * @return void
	 */
	public static function download(string $filename)
	{
		$arrType = [
			'csv' => ['type' => 'text/csv', 'encoding' => 'Ascii'],
			'doc' => ['type' => 'application/msword', 'encoding' => 'Binary'],
			'html' => ['type' => 'text/html', 'encoding' => 'Ascii'],
			'json' => ['type' => 'application/json', 'encoding' => 'Ascii'],
			'pdf' => ['type' => 'application/pdf', 'encoding' => 'Binary'],
			'xls' => ['type' => 'application/vnd.ms-excel', 'encoding' => 'Binary']
		];

		if (file_exists($filename)) {
			// Get the file extension
			$extension = pathinfo($filename, PATHINFO_EXTENSION);

			$type = $arrType[$extension]['type'];
			$encoding = $arrType[$extension]['encoding'];

			// Output http headers
			header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
			header('Content-Description: File Transfer');
			header('Content-type: ' . $type);
			header('Content-Transfer-Encoding: ' . $encoding);
			readfile($filename);
		} else {
			throw new \InvalidArgumentException(sprintf('File %s not found', $filename));
		}
	}

	/**
	 * Confirm or not if a string is starts with ...
	 *
	 * 	startsWith('Laravel', 'Lara') ==> true
	 *
	 * @link https://stackoverflow.com/a/834355/1065340

	 * @param  string  $string The string
	 * @param  string  $prefix The prefix to search
	 * @return boolean True when the string is ending with that prefix
	 */
	private static function startsWith(string $string, string $prefix) : bool
	{
		$length = strlen($prefix);

		return boolval(substr($string, 0, $length) === $prefix);
	}

	/**
	 * Check if the $style also contains the <link> tag, if not,
	 * add the tag
	 *
	 * @param  string $style
	 * @return string
	 */
	public static function addCSSTag(string $style) : string
	{
		$style = trim($style);
		if (!self::startsWith($style, '<link')) {
			$style = '<link rel="stylesheet" href="' . $style . '" media="screen"/>';
		}

		return $style;
	}

	/**
	 * Check if the $script also contains the <script> tag, if not,
	 * add the tag
	 *
	 * @param  string $script
	 * @return string
	 */
	public static function addJSTag(string $script) : string
	{
		$script = trim($script);

		if (!self::startsWith($script, '<script')) {
			$script = '<script src="' . $script . '"></script>';
		}

		return $script;
	}

	/**
	 * Detect if the request was made with an Ajax call or not
	 *
	 * @return boolean
	 */
	public static function isAjaxRequest() : bool
	{
		$ajax = false;

		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$ajax = true;
		}

		return $ajax;
	}

	/**
	 * Parse the HTML string and remove comments
	 *
	 * Example :
	 * 	$html = '<!-- a comment --><h1>Test</h1><!-- something else -->'
	 * 	$result = removeHTMLComments($html)  ' return <h1>Test</h1>
	 *
	 * @param  string $html
	 * @return string The HTML string without comments
	 */
	public static function removeHTMLComments(string $html) : string
	{
		$pattern = [
			'/<!--.*?-->\\n?/s' // Strip HTML comments <!-- (stuff) -->
		];

		return preg_replace($pattern, '', $html);
	}
}
