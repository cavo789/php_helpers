<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 *
 * Description
 * HTML Helper
 */

namespace cavo789\Helpers;

use cavo789\Helpers\Strings as Strings;

class HTML
{
	/**
	 * Create a link ("<a href="...">...</a>)
	 *
	 * @example
	 *
	 * HTML::makeLink('www.google.be', 'Google', ['class' => 'link'], true);
	 * Will return '<a href="www.google.be" class="link" rel="noopener noreferrer">Google</a>';
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
				$extra['rel'] .= 'noreferrer ';
			}

			if (isset($extra['rel'])) {
				$extra['rel'] = trim($extra['rel']);
			}
		}

		// Flatten the array i.e. convert the array as a string like
		//		id ="download" class ="download_file" target ="_blank"
		$flattened = $extra;

		array_walk($flattened, function (&$value, $key) {
			$value = $key . '="' . $value . '"';
		});

		// Make the link
		$attribs = trim('href="' . $url . '" ' . implode(' ', $flattened));
		$link = '<a ' . $attribs . '>' . $text . '</a>';

		return $link;
	}

	/**
	 * Return the current URL
	 *
	 * @param  boolean $removeScriptName Remove the script from the URL
	 *                                   return http://localhost/BOSA/ and not
	 *                                   http://localhost/BOSA/index.php when True
	 *                                   i.e. remove "index.php"
	 * @param  boolean $removePathInfo   Remove the path after the script name
	 *                                   return http://localhost/BOSA/ and not
	 *                                   http://localhost/BOSA/index.php/API/clear
	 *                                   when True i.e. remove "/API/clear"
	 *                                   Works also when URLs are rewritten like
	 *                                   http://localhost/BOSA/API/clear,
	 *                                   http://localhost/BOSA/ will be returned
	 * @return string
	 */
	public static function getCurrentURL(
		bool $removeScriptName = true,
		bool $removePathInfo = true
	) : string {
		// Determine if it's http or https
		if (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		// Get the port isn't 80 (http) or 443 (https)
		$port = '';
		// $_SERVER['SERVER_PORT'] is not present when started from a CLI script
		if (isset($_SERVER['SERVER_PORT'])) {
			if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
				$port = ":$_SERVER[SERVER_PORT]";
			}
		}

		// Get the domain name (127.0.0.1 or localhost or a domain)
		// $_SERVER['SERVER_NAME'] is not present when started from a CLI script
		$serverName = '';
		if (isset($_SERVER['SERVER_PORT'])) {
			$serverName = $_SERVER['SERVER_NAME'];
		}

		// Get the URL (what appears after the server_name)
		$URI = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$URI = $_SERVER['REQUEST_URI'];

			// When using rewritten URLs we can have
			// http://localhost/LimeSurvey/BOSA/api/clear
			// when the real page is http://localhost/LimeSurvey/BOSA/index.php
			// Removing PathInfo should remove "/api/clear" since this is, also,
			// a parameter
			if ($removePathInfo) {
				$URI = dirname($_SERVER['SCRIPT_NAME']);
			}
		}

		$pageURL = $scheme . '://' . $serverName . $port . $URI;

		// Do we need to remove the name of the script. If true, don't
		// return http://localhost/site/index.php but
		// http://localhost/site/
		if ($removeScriptName) {
			$script = basename($_SERVER['SCRIPT_NAME']);
			$pageURL = str_replace('/' . $script, '', $pageURL);
			$pageURL = rtrim($pageURL, '/') . '/';
		}

		if (($removeScriptName) && (isset($_SERVER['QUERY_STRING']))) {
			$pageURL = str_replace('?' . $_SERVER['QUERY_STRING'], '', $pageURL);
			$pageURL = rtrim($pageURL, '/') . '/';
		}

		if ($removePathInfo) {
			// PATH_INFO is a suffix added to the script like in
			//		...index.php/download/xxxx
			// as used in API calls
			if (isset($_SERVER['PATH_INFO'])) {
				$pageURL = str_replace($_SERVER['PATH_INFO'], '', $pageURL);
			}
		}

		return $pageURL;
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

			return $url . '?' . $queryString;
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
	 * @return string
	 */
	public static function getParameter(
		string $name,
		$default = '',
		string $filter = 'string',
		int $source = INPUT_GET
	) : string {
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

		return strval($value);
	}

	/**
	 * Get a posted variable's value
	 *
	 * @param  string $name
	 * @param  string $default
	 * @param  string $filter
	 * @return string
	 */
	public static function getFormVariable(
		string $name,
		$default = '',
		string $filter = 'string'
	) : string {
		$value = self::getParameter($name, $default, $filter, INPUT_POST);

		return $value;
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
	 * Check if the $style also contains the <link> tag, if not,
	 * add the tag
	 *
	 * @example
	 *
	 * HTML::addCSSTag('style.css');
	 * Will return '<link rel="stylesheet" href="style.css" media="screen"/>';
	 *
	 * @param  string $style
	 * @return string
	 */
	public static function addCSSTag(string $style) : string
	{
		$style = trim($style);
		if (!Strings::startsWith($style, '<link')) {
			$style = '<link rel="stylesheet" href="' . $style . '" media="screen"/>';
		}

		return $style;
	}

	/**
	 * Check if the $script also contains the <script> tag, if not,
	 * add the tag
	 *
	 * @example
	 *
	 * HTML::addJSTag('script.js');
	 * Will return <script type="text/javascript" src="script.js"></script>
	 *
	 * @param  string $script
	 * @return string
	 */
	public static function addJSTag(string $script) : string
	{
		$script = trim($script);

		if (!Strings::startsWith($script, '<script')) {
			$script = '<script type="text/javascript" src="' . $script . '"></script>';
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
	 * @example
	 *
	 * $value = '<!-- a comment --><h1>Test</h1><!-- something else -->';
	 *	HTML::removeHTMLComments($value);
	 * Will return '<h1>Test</h1>';
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

	/**
	 * Convert as CSV string into a HTML table
	 *
	 * @suppress PhanUnusedVariable
	 *
	 * @example
	 *
	 * $csv = "col1;col2;col3\nrow1-1;row1-2;row1-3;\nrow2-1;row2-2;row2-3";
	 *
	 * $value = HTML::csv2table($csv);
	 *
	 * Will return
	 *		'<table><thead><tr><th>col1</th><th>col2</th><th>col3</th></tr></thead>' .
	 * 	'<tbody><tr><td>row1-1</td><td>row1-2</td><td>row1-3</td><td></td></tr>' .
	 *		'<tr><td>row2-1</td><td>row2-2</td><td>row2-3</td></tr></tbody></table>';
	 *
	 * @param string $sCSV  A comma separated content	 *
	 * @param array  $extra Optional
	 *                      - "id"					ID to use for the table
	 *                      - "class"				CLASS to add in the table tag
	 *                      - "class_enhanced"	CLASS to add only when enhanced=1
	 *                      - "enhanced"	Just a simple raw table
	 *                      or with extra features like tfoot, classes, ...?
	 *                      - "anything else" (f.i. "style", "grid", "role", ...)
	 *
	 * @return string
	 */
	public static function csv2table(
		string $sCSV,
		array $extra = []
	) : string {
		// If the CSV is empty, nothing to do
		if (trim($sCSV) == '') {
			return '';
		}

		// Just a simple raw table (very basic) or with extra
		// features like with classes, tfoot, ...?
		$enhanced = boolval($extra['enhanced'] ?? false);
		if (isset($extra['enhanced'])) {
			unset($extra['enhanced']);
		}

		// Get extra infos if present and don't allow the presence of
		// double-quotes in the value to not broke our HTML tag
		$tblID = str_replace('"', '', trim($extra['id'] ?? ''));
		if ($tblID !== '') {
			$tblID = ' id="' . $tblID . '"';
			unset($extra['id']);
		}

		$tblClassEnhanced = '';
		if ($enhanced) {
			$tblClassEnhanced = str_replace('"', '', trim($extra['class_enhanced'] ?? ''));
			if ($tblClassEnhanced !== '') {
				unset($extra['class_enhanced']);
			}
		}

		$tblClass = str_replace('"', '', trim($extra['class'] ?? ''));
		if ($tblClass !== '') {
			unset($extra['class']);
		}

		if (($tblClass !== '') || ($tblClassEnhanced !== '')) {
			$tblClass = ' class="' . trim($tblClass . ' ' . $tblClassEnhanced) . '"';
		}

		// The $extra array contains perhaps other entries like, f.i.,
		//		"style"=>"display:none", "role"=>"grid", ...
		// Get all remaining entries and generate an "attributes" string
		$attributes = '';
		if ($enhanced) {
			// Only with an enhanced table; not needed for a "stupid" raw table
			if (count($extra) > 0) {
				foreach ($extra as $key => $value) {
					$attributes .= $key . '="' . str_replace('"', '', $value) . '" ';
				}
				$attributes = ' ' . trim($attributes);
			}
		}

		// Cleansing
		$sCSV = str_replace('&quot;', '"', $sCSV);

		// Parse the rows
		$rows = str_getcsv($sCSV, "\n");

		// Our <table>
		$table = '';

		// Output the list of fields name
		$line = '';

		// Get the headings from the first line
		$header = str_getcsv(trim($rows[0], '"'), ';', '"');

		// $header is an associative array. $key is a number and
		// $field the name of the field
		foreach ($header as $key => $field) {
			$line .= '<th>' . trim($field, '"') . '</th>';
		}

		// Add tfoot only for enhanced table
		$table .= '<thead><tr>' . $line . '</tr></thead>' . ($enhanced ? '<tfoot><tr>' . $line . '</tr></tfoot>' : '') .
			'<tbody>';

		// Remove the first entry in the array so remove the heading rows
		// (since already processed)
		array_shift($rows);

		// Process each lines i.e. all the records (=> answers)
		foreach ($rows as $row) {
			// Don't process empty rows
			if (trim($row) == '') {
				continue;
			}

			// Convert value;value;value into an array
			$row = str_getcsv($row, ';');

			$line = '';
			foreach ($row as $key => $value) {
				// Can't be "null", should be a string
				$value = is_null($value) ? '' : $value;

				// Make a few cleaning
				$line .= '<td>' . Strings::cleansing($value) . '</td>';
			}
			$table .= '<tr>' . $line . '</tr>';
		}

		$table .= '</tbody>';

		// Add the table tag.
		$table =
			'<table' .
			$tblID .
			$tblClass .
			$attributes .
			'>' .
			$table .  // The table content
		'</table>';

		return $table;
	}

	/**
	 * In HTML, tabs, spaces, linefeed (LF) and carriage returns (CR) are not needed
	 * between HTML tags. This function will remove them making the HTML code more compact.
	 *
	 * @example
	 *
	 * $value =
	 *		'<section>' .
	 *		'		<div class="container">		' .
	 *		'					<div class="row">' .
	 *		'<!-- CONTENT --><p>Main content</p></div></div></section>';
	 *
	 *	HTML::compress($value) will return <section><div class="container">
	 *		<div class="row"><!-- CONTENT --><p>Main content</p></div></div></section>
	 *
	 * Every unneeded spaces between tags are removed
	 *
	 * @see https://github.com/padosoft/support/blob/master/src/string.php#L714
	 *
	 * @param  string $value HTML string
	 * @return string Same string but without unneeded spaces between tags
	 */
	public static function compress(string $value) : string
	{
		return preg_replace(
			['/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s'],
			['>', '<', '\\1'],
			$value
		);
	}
}
