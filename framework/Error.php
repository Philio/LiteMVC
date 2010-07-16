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
	const Header_Prefix = 'HTTP/1.1 ';
	const Header_OK     = '200 OK';
	const Header_Fatal  = '500 Internal Server Error';
	
	/**
	 * Display errors
	 * 
	 * @var bool
	 */
	protected $_display = false;
	
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
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function  __construct(App $app) {
		// Read any config options
		$config = $app->getResource('Config');
		if (!is_null($config->error)) {
			$errConfig = $config->error;
			// Set display
			if (isset($errConfig['display']) && $errConfig['display'] == true) {
				$this->setDisplay(true);
			}
			// Set template
			if (isset($errConfig['template'])) {
				$this->setTemplate($errConfig['template']);
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
			'level'   => $level,
			'code'    => $code,
			'message' => $message,
			'file'    => $file,
			'line'    => $line,
			'trace'   => $trace
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
		header(self::Header_Prefix . self::Header_Fatal);
		// Compile the log
		$output = '';
		if ($this->_display || true) {
			$output = '<h2>Error Log</h2>';
			$log = array_reverse($this->_log);
			foreach($log as $entry) {
				$output .= '<b>' . $entry['level'] .'</b>: ' . ucfirst($entry['message']) .
					'<br /><b>Occured in</b>: ' . $entry['file'] . ' at line ' . $entry['line'] .
					'.<br />';
				if (!is_null($entry['trace'])) {
					$output .= '<b>Stack trace</b>:<br />' . nl2br($entry['trace'], true) . '<br />';
				}
				$output .= '<br />';
			}
		}
		// Use template if one exists
		if ($this->_template && file_exists(\PATH . $this->_template)) {
			$page = file_get_contents(\PATH . $this->_template);
			$page = str_replace('{LOG}', $output, $page);
		} else {
			$page = '<h1>LiteMVC - A fatal error occured</h1>' . $output;
		}
		// Output error
		echo $page;
		exit;
	}
	
}