<?php

/**
 * Christophe Avonture
 * Written date : 2018-09-13
 * Last modified:
 *
 * Inspiration from
 * @link https://www.codepunker.com/blog/handling-php-errors-with-class
 *
 * Description
 * Just include this classe at the very top of your script and every runtime
 * errors will be captured and displayed through this file.
 *
 * Use it
 *
 * require_once('Error.php');
 * $error = new \Classes\Error();
 *
 * or
 *
 * // Setting the HTML to display
 *	$error = new \Classes\Error('<h1>Houston we've a problem</h1>');
 *
 * or
 *
 * // HTML template using the tag so the error will be inserted there
 * $error = new \Classes\Error("<h1>Houston we' ve a problem </h1>".
 * 	"<div>{{ error_message }}</div><div>Please email us</div>");
 *
 * or
 *
 * // The HTML template is a filename not inline HTML
 * $error = new \Classes\Error(__DIR__ . '/templates/error.html')
 *
 * See the other parameters for more customization
 *
 */

declare(strict_types=1);

namespace cavo789\Classes;

class Error
{
	private $startTime = 0;
	private $template = '';
	private $timezone = '';
	private $dateFormat = '';
	private $tag_message = '{{ error_message }}';
	private $tag_code = '{{ error_code }}';
	private $tag_title = '{{ error_title }}';

	private $http_returned_errorcode = 400;
	private $http_returned_errortitle = 'Bad Request';

	// When no template are specified, the error will be displayed using this one:
	private $defaultTemplate = '<pre style="background-color:orange;padding:25px;">%s</pre>';

	/**
	 * Initialize the class
	 *
	 * @param string $template    HTML string or full name of the file to use as template
	 *                            for displaying errors
	 * @param string $timezone    Timezone like Europe/Paris
	 * @param string $dateFormat  How to display date/time (f.i. D M Y H:i:s)
	 * @param string $tag_message When using a template, text to search for replacing it
	 *                            by the error message (default is {{ error_message }})
	 * @param string $tag_code    When using a template, the error code will be inserted
	 *                            when the mentioned tag is found (default is {{ error_code }})
	 * @param string $tag_title   When using a template, the error code will be inserted
	 *                            when the mentioned title is found (default is {{ error_title }})
	 */
	public function __construct(
		string $template = '',
		string $timezone = 'Europe/Brussels',
		string $dateFormat = 'd M Y H:i:s',
		string $tag_message = '{{ error_message }}',
		string $tag_code = '{{ error_code }}',
		string $tag_title = '{{ error_title }}'
	) {
		$this->startTime = microtime(true);

		// Can be empty (no template), a valid HTML string or a filename
		$this->template = trim($template);

		// Use a specific timezone for displaying correctly hours
		$this->timezone = $timezone;

		// Format for displaying hours
		$this->dateFormat = $dateFormat;

		// When using a template, the position where the error message should be
		// inserted is, by default, defined by the "{{ error_message }}" tag. That tag
		// can be changed using the $tag parameter.
		if (trim($tag_message) !== '') {
			$this->tag_message = $tag_message;
		}
		// Same for the code
		if (trim($tag_code) !== '') {
			$this->tag_code = $tag_code;
		}
		// Same for the title
		if (trim($tag_title) !== '') {
			$this->tag_title = $tag_title;
		}

		ob_start();
		ini_set('display_errors', 'on');
		error_reporting(E_ALL);

		// Register our handler
		set_error_handler([$this, 'scriptError']);

		// And remove it when the script is being finished
		register_shutdown_function([$this, 'shutdown']);
	}

	/**
	 * Check if the http request is an AJAX call
	 *
	 * @return boolean
	 */
	private function is_ajax(): bool
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			(strtolower(getenv('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest');
	}

	/**
	 * Return a HTML string that is the html of a element. Can return the
	 * outer HTML or the inner one.
	 *
	 * Consider $element equal to
	 *    <section>
	 *       <h2>A slide with a background image</h2>
	 *       <p>...</p>
	 *    </section>
	 *
	 * $outer = true will return also the section tag
	 * $outer = false won't return the section; only the children html
	 *
	 * @see https://stackoverflow.com/a/5404966/1065340
	 *
	 * @param  \DOMNode $element A DOM element (can be a <p>, <h.>, <div>, ...)
	 * @param  boolean  $outer   When true, the element itself is taken
	 * @return string   The outer HTML of the element
	 */
	public function getDOMHTML(\DOMNode $element, bool $outer = true) : string
	{
		$dom = new \DOMDocument('1.0');

		$dom->preserveWhiteSpace = false;
		$dom->encoding = 'utf-8';
		$b = $dom->importNode($element->cloneNode(true), true);
		$dom->appendChild($b);
		$html = $dom->saveHTML();

		if (!$outer) {
			// Return the INNER html => remove the first tag
			$html = substr(
				$html,
				strpos($html, '>') + 1,
				-(strlen($element->nodeName) + 4)
			);
		}

		return $html;
	}

	/**
	 * An error has been encountered, display it
	 *
	 * @param  integer $errno
	 * @param  string  $errstr
	 * @param  string  $errfile
	 * @param  integer $errline
	 * @return void
	 */
	public function scriptError(int $errno, string $errstr, string  $errfile, int $errline)
	{
		if (!headers_sent()) {
			header('HTTP/1.1 ' . $this->http_returned_errorcode . ' ' .
				$this->http_returned_errortitle);
		}

		if (ob_get_contents() !== false) {
			ob_end_clean();
		}

		switch ($errno) {
			case E_ERROR:
				$errseverity = 'Error';
				break;
			case E_WARNING:
				$errseverity = 'Warning';
				break;
			case E_NOTICE:
				$errseverity = 'Notice';
				break;
			case E_CORE_ERROR:
				$errseverity = 'Core Error';
				break;
			case E_CORE_WARNING:
				$errseverity = 'Core Warning';
				break;
			case E_COMPILE_ERROR:
				$errseverity = 'Compile Error';
				break;
			case E_COMPILE_WARNING:
				$errseverity = 'Compile Warning';
				break;
			case E_USER_ERROR:
				$errseverity = 'User Error';
				break;
			case E_USER_WARNING:
				$errseverity = 'User Warning';
				break;
			case E_USER_NOTICE:
				$errseverity = 'User Notice';
				break;
			case E_STRICT:
				$errseverity = 'Strict Standards';
				break;
			case E_RECOVERABLE_ERROR:
				$errseverity = 'Recoverable Error';
				break;
			case E_DEPRECATED:
				$errseverity = 'Deprecated';
				break;
			case E_USER_DEPRECATED:
				$errseverity = 'User Deprecated';
				break;
			default:
				$errseverity = 'Error';
				break;
		}

		// Set timezone and date to match our configuration
		date_default_timezone_set($this->timezone);
		$Date = date($this->dateFormat);

		// Build the error string
		$error = $Date . '<br/>' .
			'<span style="color:red;font-weight:bold;">' . $errseverity . ':</span>&nbsp;' .
			$errstr . '<br/>' .
			'<span style="color:#3D9700;">Line ' . $errline . ': ' . $errfile . '</span>' .
			'<br/><br/>';

		// Default template when none has been specified in the constructor
		$html = sprintf($this->defaultTemplate, $this->tag_message);

		if ($this->template !== '') {
			// A template has been specified; is it a file or a string?
			if (file_exists($this->template)) {
				try {
					$html = file_get_contents($this->template);
				} catch (Exception $e) {
					$html = $this->tag_message;
				}
			} else {
				$html = $this->template;
			}
		}

		if (strpos($html, $this->tag_message) !== false) {
			$html = str_replace($this->tag_message, trim($error), $html);
		} else {
			$html .= trim($error);
		}

		$html = str_replace($this->tag_code, $this->http_returned_errorcode, $html);
		$html = str_replace($this->tag_title, $this->http_returned_errortitle, $html);

		/**
		 * Detect if the request was made by an AJAX call and if yes,
		 * detect if the HTML template contains a "main".
		 * If still yes, extract that portion (only the <div class="main">...</div>)
		 * element so, when using Ajax, we will not send http tags like meta, title, ...
		 * nor body, script and style; only the required info i.e. the error message
		 */
		if (self::is_ajax()) {
			$dom = new \DomDocument();
			@libxml_use_internal_errors(true);

			$dom->loadHTML($html);
			// Search for a class called "main"
			$class_name = 'main';
			$xpath = new \DomXPath($dom);

			$nodeList = $xpath->query("//div[contains(@class, '" . $class_name . "')]");

			if ($nodeList->length > 0) {
				// If found, extract the outer html for that element
				$html = self::getDOMHTML($nodeList->item(0));
			}
		}

		echo $html;
	}

	public function shutdown()
	{
		$isError = false;
		if ($error = error_get_last()) {
			switch ($error['type']) {
				case E_ERROR:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
				case E_RECOVERABLE_ERROR:
				case E_CORE_WARNING:
				case E_COMPILE_WARNING:
					$isError = true;
					$this->scriptError($error['type'], $error['message'], $error['file'], $error['line']);
					break;
			}
		}
	}
}
