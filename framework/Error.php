<?php
/**
 * LiteMVC Application Framework
 * 
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC;

class Error
{

	/**
	 * Error codes (PHP errors)
	 * 
	 * @var array
	 */
	protected $_codes = array(
		\E_ERROR             => 'PHP Error',
		\E_WARNING           => 'PHP Warning',
		\E_PARSE             => 'PHP Parse Error',
		\E_NOTICE            => 'PHP Notice',
		\E_CORE_ERROR        => 'PHP Core Error',
		\E_CORE_WARNING      => 'PHP Core Warning',
		\E_COMPILE_ERROR     => 'PHP Compile Error',
		\E_COMPILE_WARNING   => 'PHP Compile Warning',
		\E_USER_ERROR        => 'PHP User Error',
		\E_USER_WARNING      => 'PHP User Warning',
		\E_USER_NOTICE       => 'PHP User Notice',
		\E_STRICT            => 'PHP Strict',
		\E_RECOVERABLE_ERROR => 'PHP Recoverable Error',
		\E_DEPRECATED        => 'PHP Deprecated',
		\E_USER_DEPRECATED   => 'PHP User Deprecated'
	);
	
	/**
	 * Error codes that should be treated as fatal errors
	 * 
	 * @var array
	 */
	protected $_fatal = array(
		\E_ERROR,
		\E_CORE_ERROR,
		\E_COMPILE_ERROR,
		\E_USER_ERROR
	);
	
	/**
	 * Display errors
	 * 
	 * @var bool
	 */
	protected $_display = false;

	/**
	 * Error mode
	 *
	 * @var string
	 */
	protected $_mode = self::MODE_HTML;
	
	/**
	 * Error template
	 * 
	 * @var string
	 */
	protected $_template;
	
	/**
	 * Have any errors occured
	 * 
	 * @var bool
	 */
	protected $_errors = false;
	
	/**
	 * The error log
	 * 
	 * @var array
	 */
	protected $_log = array();

	/**
	 * Error code for exception
	 *
	 * @var string
	 */
	const E_EXCEPTION = 'Exception';

	/**
	 * Header constants
	 *
	 * @var string
	 */
	const HEADER_PREFIX	= 'HTTP/1.1 ';
	const HEADER_OK		= '200 OK';
	const HEADER_FATAL	= '500 Internal Server Error';

	/**
	 *
	 */

	/**
	 * Error mode constants
	 *
	 * @var string
	 */
	const MODE_HTML	= 'HTML';
	const MODE_JSON	= 'JSON';

	/**
	 * Logging key names
	 *
	 * @var string
	 */
	const LOG_LEVEL	= 'level';
	const LOG_CODE	= 'code';
	const LOG_MSG	= 'message';
	const LOG_FILE	= 'file';
	const LOG_LINE	= 'line';
	const LOG_TRACE	= 'trace';

	/**
	 * Logging HTML
	 *
	 * @var string
	 */
	const HTML_LOG		= '<b>{level}</b>: {message}<br /><b>Occurred in</b>: {file} at line {line}.<br />';
	const HTML_TRACE	= '<b>Stack trace</b>:<br />{trace}<br />';

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_DISPLAY	= 'display';
	const CONF_MODE		= 'mode';
	const CONF_TEMPLATE	= 'template';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_CONFIG	= 'Config';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function  __construct(App $app) {
		// Read any config options
		$config = $app->getResource(self::RES_CONFIG);
		if (!is_null($config->error)) {
			$errConfig = $config->error;
			// Set display
			if (isset($errConfig[self::CONF_DISPLAY])) {
				$this->setDisplay($errConfig[self::CONF_DISPLAY]);
			}
			// Set mode
			if (isset($errConfig[self::CONF_MODE])) {
				$this->setMode($errConfig[self::CONF_MODE]);
			}
			// Set template'mode'
			if (isset($errConfig[self::CONF_TEMPLATE])) {
				$this->setTemplate($errConfig[self::CONF_TEMPLATE]);
			}
		}
		// Register handlers
		$this->register();
	}
	
	/**
	 * Register handlers
	 * 
	 * @return void
	 */
	public function register()
	{
		set_error_handler(array($this, 'errorHandler'), E_ALL);
		set_exception_handler(array($this, 'exceptionHandler'));
	}
	
	/**
	 * Unregister handlers
	 * 
	 * @return void
	 */
	public function unregister()
	{
		restore_error_handler();
		restore_exception_handler();
	}
	
	/**
	 * Set error display
	 * 
	 * @param bool $display
	 * @return void
	 */
	public function setDisplay($display)
	{
		$this->_display = $display;
	}

	/**
	 * Set display mode
	 *
	 * @param string $mode
	 * @return void
	 */
	public function setMode($mode)
	{
		if ($mode == self::MODE_HTML || $mode = self::MODE_JSON) {
			$this->_mode = $mode;
		}
	}
	
	/**
	 * Set error template
	 * 
	 * @param string $template
	 * @return void
	 */
	public function setTemplate($template)
	{
		$this->_template = $template;
	}
	
	/**
	 * Error handler callback
	 * 
	 * @param int $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param int $errline
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		// Check for fatal error
		if (in_array($errno, $this->_fatal)) {
			// Throw ErrorException for fatal errors (enables trace)
			throw new ErrorException($errstr, $errno, $errno, $errfile, $errline);
		} else {
			// Log non-fatal errors
			$this->logError(
				$this->_codes[$errno],
				$errno,
				$errstr,
				$errfile,
				$errline
			);
		}
	}
	
	/**
	 * Exception handler callback
	 * 
	 * @param Exception $e
	 */
	public function exceptionHandler($e)
	{
		// Check error code (not 0 = ErrorException)
		if (isset($this->_codes[$e->getCode()])) {
			$level = $this->_codes[$e->getCode()];
		} else {
			$level = self::E_EXCEPTION;
		}
		// Record to the log
		$this->logError(
			$level,
			$e->getCode(),
			$e->getMessage(),
			$e->getFile(),
			$e->getLine(),
			$e->getTraceAsString()
		);
		// All uncaught exceptions are fatal, display error page
		$this->fatal();
	}
	
	/**
	 * Record an error to the log
	 * 
	 * @param string $level
	 * @param int $code
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 * @param string $trace
	 */
	public function logError($level, $code, $message, $file, $line, $trace = null)
	{
		$this->_log[] = array(
			self::LOG_LEVEL	=> $level,
			self::LOG_CODE	=> $code,
			self::LOG_MSG	=> $message,
			self::LOG_FILE	=> $file,
			self::LOG_LINE	=> $line,
			self::LOG_TRACE	=> $trace
		);
		$this->_errors = true;
	}
	
	/**
	 * Check if errors have occured
	 * 
	 * @return bool
	 */
	public function hasErrors()
	{
		return $this->_errors;
	}
	
	/**
	 * Get error log
	 * 
	 * @return mixed
	 */
	public function getLog()
	{
		if ($this->hasErrors()) {
			return $this->_log;
		}
		return false;
	}
	
	/**
	 * Fatal error handling
	 * 
	 * @return void
	 */
	protected function fatal()
	{
		// Send header for fatal error
		header(self::HEADER_PREFIX . self::HEADER_FATAL);
		// Compile the log
		switch ($this->_mode) {
			case self::MODE_HTML:
				$page = $this->HTML();
				break;
			case self::MODE_JSON:
				$page = $this->JSON();
				break;
		}
		// Output error
		echo $page;
		exit;
	}

	/**
	 * Produce friendly HTML error with/without template file
	 *
	 * @return string
	 */
	protected function HTML()
	{
		$output = '';
		if ($this->_display) {
			$output = '<h2>Error Log</h2>' . PHP_EOL;
			$output .= '<p>' . PHP_EOL;
			$log = array_reverse($this->_log);
			foreach($log as $entry) {
				// Compile log entry
				$entryHtml = self::HTML_LOG;
				$entryHtml = str_replace('{' . self::LOG_LEVEL . '}', $entry[self::LOG_LEVEL], $entryHtml);
				$entryHtml = str_replace('{' . self::LOG_MSG . '}', $entry[self::LOG_MSG], $entryHtml);
				$entryHtml = str_replace('{' . self::LOG_FILE . '}', $entry[self::LOG_FILE], $entryHtml);
				$entryHtml = str_replace('{' . self::LOG_LINE . '}', $entry[self::LOG_LINE], $entryHtml);
						
				// Include trace if there is one
				if (isset($entry[self::LOG_TRACE])) {
					$entryHtml .= self::HTML_TRACE;
					$entryHtml = str_replace('{' . self::LOG_TRACE . '}', nl2br($entry[self::LOG_TRACE], true), $entryHtml);
				}

				// Append to output
				$output .= $entryHtml;
				$output .= '<br />' . PHP_EOL;
			}
		}

		// Use template if one exists
		if ($this->_template && file_exists(\PATH . $this->_template)) {
			$page = file_get_contents(\PATH . $this->_template);
			$page = str_replace('{LOG}', $output, $page);
		} else {
			$page = '<h1>A fatal error occurred</h1>' . $output;
		}
		return $page;
	}

	/**
	 * Produce error in JSON
	 *
	 * @return string
	 */
	protected function JSON()
	{
		$log = array_reverse($this->_log);
		if ($this->_display) {
			return json_encode(
				array(
					'error'   => $log[0][self::LOG_CODE],
					'message' => $log[0][self::LOG_MSG]
				)
			);
		} else {
			return json_encode(
				array(
					'error' => $log[0][self::LOG_CODE]
				)
			);
		}
	}
	
}