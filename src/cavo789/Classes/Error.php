<?php

declare(strict_types=1);

/**
 * Christophe Avonture
 * Written date : 2018-09-13

 * Description
 * Just include this class at the very top of your script and every runtime
 * errors will be captured and displayed through this file.
 *
 * Because this class can be instantiated in more than one script
 * of the same application, the class is a Singleton: only one instance
 * will be instantiated and loaded into memory.
 *
 * Use it
 *
 * require_once('Error.php');
 * $error = Error::getInstance();
 *
 * or
 *
 * // Setting the HTML to display
 *  $error = Error::getInstance('<h1>Houston we've a problem</h1>');
 *
 * or
 *
 * // HTML template using the tag so the error will be inserted there
 * $error = Error::getInstance("<h1>Houston we' ve a problem </h1>".
 *  "<div>{{ error_message }}</div><div>Please email us</div>");
 *
 * or
 *
 * // The HTML template is a filename not inline HTML
 * $error = Error::getInstance(__DIR__ . '/templates/error.html')
 *
 * See the other parameters for more customization
 *
 * Inspiration from
 *
 * @link https://www.codepunker.com/blog/handling-php-errors-with-class
 */

namespace cavo789\Classes;

/**
 * @suppress PhanCompatibleVoidTypePHP70
 */
class Error
{
    /**
     * Capture the microtime when this class is fired.
     *
     * @var float
     * @access private
     */
    private $startTime = 0;

    /**
     * HTML string or filename that contains the template for
     * displaying an error.
     *
     * @var string
     */
    private $template = '';

    /**
     * Timezone to use for displaying date/times.
     * For instance 'Europe/Brussels'.
     *
     * @var string
     * @access private
     */
    private $timezone = '';

    /**
     * How to display date/time (f.i. D M Y H:i:s).
     *
     * @var string
     * @access private
     */
    private $dateFormat = '';

    /**
     * The tag to search for where to put the error message.
     *
     * @var string
     * @access private
     */
    private $tagMessage = '{{ error_message }}';

    /**
     * The tag to search for where to put the error code.
     *
     * @var string
     * @access private
     */
    private $tagCode = '{{ error_code }}';

    /**
     * The tag to search for where to put the error title.
     *
     * @var string
     * @access private
     */
    private $tagTitle = '{{ error_title }}';

    /**
     * Error code to sent with the HTTP headers.
     *
     * @var int
     * @access private
     */
    private $httpReturnedErrorCode = 400;

    /**
     * Associated text for the HTTP error code.
     *
     * @var string
     * @access private
     */
    private $httpReturnedErrorTitle = 'Bad Request';

    /**
     * When no template are specified, the error will be displayed
     * using the following one.
     *
     * @var string
     * @access private
     */
    private $defaultTemplate = '<pre style="background-color:orange;padding:25px;">%s</pre>';

    /**
     * @var Error
     * @access private
     * @static
     */
    private static $instance = null;

    /**
     * Initialize the class.
     *
     * @param string $template   HTML string or full name of the file to use as template
     *                           for displaying errors
     * @param string $timezone   Timezone like Europe/Paris
     * @param string $dateFormat How to display date/time (f.i. D M Y H:i:s)
     * @param string $tagMessage When using a template, text to search for replacing it
     *                           by the error message (default is {{ error_message }})
     * @param string $tagCode    When using a template, the error code will be inserted
     *                           when the mentioned tag is found (default is {{ error_code }})
     * @param string $tagTitle   When using a template, the error code will be inserted
     *                           when the mentioned title is found (default is {{ error_title }})
     */
    private function __construct(
        string $template = '',
        string $timezone = 'Europe/Brussels',
        string $dateFormat = 'd M Y H:i:s',
        string $tagMessage = '{{ error_message }}',
        string $tagCode = '{{ error_code }}',
        string $tagTitle = '{{ error_title }}'
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
        if (trim($tagMessage) !== '') {
            $this->tagMessage = $tagMessage;
        }
        // Same for the code
        if (trim($tagCode) !== '') {
            $this->tagCode = $tagCode;
        }
        // Same for the title
        if (trim($tagTitle) !== '') {
            $this->tagTitle = $tagTitle;
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
     * Load an instance of the class.
     *
     * @param string $template   HTML string or full name of the file to use as template
     *                           for displaying errors
     * @param string $timezone   Timezone like Europe/Paris
     * @param string $dateFormat How to display date/time (f.i. D M Y H:i:s)
     * @param string $tagMessage When using a template, text to search for replacing it
     *                           by the error message (default is {{ error_message }})
     * @param string $tagCode    When using a template, the error code will be inserted
     *                           when the mentioned tag is found (default is {{ error_code }})
     * @param string $tagTitle   When using a template, the error code will be inserted
     *
     * @return Error
     */
    public static function getInstance(
        string $template = '',
        string $timezone = 'Europe/Brussels',
        string $dateFormat = 'd M Y H:i:s',
        string $tagMessage = '{{ error_message }}',
        string $tagCode = '{{ error_code }}',
        string $tagTitle = '{{ error_title }}'
    ) : Error {
        if (null == self::$instance) {
            self::$instance = new Error(
                $template,
                $timezone,
                $dateFormat,
                $tagMessage,
                $tagCode,
                $tagTitle
            );
        }

        return self::$instance;
    }

    /**
     * Check if the http request is an AJAX call.
     *
     * @return bool
     */
    private function isAjax() : bool
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
     * @param \DOMNode $element A DOM element (can be a <p>, <h.>, <div>, ...)
     * @param bool     $outer   When true, the element itself is taken
     *
     * @return string The outer HTML of the element
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
     * An error has been encountered, display it.
     *
     * @suppress PhanUnusedVariableCaughtException
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     *
     * @return void
     */
    public function scriptError(int $errno, string $errstr, string $errfile, int $errline)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 ' . $this->httpReturnedErrorCode . ' ' .
                $this->httpReturnedErrorTitle);
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
        $html = sprintf($this->defaultTemplate, $this->tagMessage);

        if ($this->template !== '') {
            // A template has been specified; is it a file or a string?
            if (file_exists($this->template)) {
                try {
                    $html = file_get_contents($this->template);
                } catch (\Exception $e) {
                    $html = $this->tagMessage;
                }
            } else {
                $html = $this->template;
            }
        }

        if (strpos($html, $this->tagMessage) !== false) {
            $html = str_replace($this->tagMessage, trim($error), $html);
        } else {
            $html .= trim($error);
        }

        $html = str_replace($this->tagCode, strval($this->httpReturnedErrorCode), $html);
        $html = str_replace($this->tagTitle, $this->httpReturnedErrorTitle, $html);

        /*
         * Detect if the request was made by an AJAX call and if yes,
         * detect if the HTML template contains a "main".
         * If still yes, extract that portion (only the <div class="main">...</div>)
         * element so, when using Ajax, we will not send http tags like meta, title, ...
         * nor body, script and style; only the required info i.e. the error message
         */
        if (self::isAjax()) {
            $dom = new \DOMDocument();
            @libxml_use_internal_errors(true);

            $dom->loadHTML($html);
            // Search for a class called "main"
            $className = 'main';
            $xpath = new \DOMXPath($dom);

            $nodeList = $xpath->query("//div[contains(@class, '" . $className . "')]");

            if ($nodeList->length > 0) {
                // If found, extract the outer html for that element
                $html = self::getDOMHTML($nodeList->item(0));
            }
        }

        echo $html;
    }

    /**
     * The execution of the PHP script has been stopped.
     * If due to a catchable error, display an error message.
     *
     * @return void
     */
    public function shutdown()
    {
        if ($error = error_get_last()) {
            switch ($error['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                case E_CORE_WARNING:
                case E_COMPILE_WARNING:
                    $this->scriptError($error['type'], $error['message'], $error['file'], $error['line']);
                    break;
            }
        }
    }
}
