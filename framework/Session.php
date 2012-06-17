<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC;

// Namespace aliases
use LiteMVC\App\Resource;
use LiteMVC\Session as Session;

class Session extends Resource
{

	/**
	 * App instance
	 *
	 * @var App
	 */
	protected $_app;

	/**
	 * Config data
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Session handlers
	 *
	 * @var array
	 */
	protected $_handlers = array();

	/**
	 * Session expire time
	 *
	 * @var int
	 */
	protected $_expires;

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_HANDLER = 'handler';
	const CONF_EXPIRES = 'expires';
	const CONF_FILE = 'file';
	const CONF_MEMCACHE = 'memcache';
	const CONF_DATABASE = 'database';

	/**
	 * Handler keys
	 *
	 * @var string
	 */
	const HANDLER_FILE = 'File';
	const HANDLER_MEMCACHE = 'Memcache';
	const HANDLER_DATABASE = 'Database';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_FILE = 'Cache\File';
	const RES_MEMCACHE = 'Cache\Memcache';
	const RES_DATABASE = 'Database';

	/**
	 * Default session length
	 *
	 * @var int
	 */
	const SESSION_EXPIRY = 3600;

	/**
	 * Constructor
	 *
	 * @param App $this->_app
	 * @return void
	 */
	public function  __construct(App $app) {
		// Set app
		$this->_app = $app;

		// Check config
		$this->_config = $this->_app->getResource('Config')->session;
		if (is_null($this->_config)) {
			throw new Session\Exception('No session configuration has been specified.');
		}
	}

	/**
	 * Initialise the resource
	 *
	 * @return void
	 */
	public function init()
	{
		// Load handlers
		if (!isset($this->_config[self::CONF_HANDLER])) {
			throw new Session\Exception('No session handlers specified in configuration.');
		}

		// Make sure handler is an array
		if (!is_array($this->_config[self::CONF_HANDLER])) {
			$this->_config[self::CONF_HANDLER] = array($this->_config[self::CONF_HANDLER]);
		}

		// Initialise handlers
		foreach ($this->_config[self::CONF_HANDLER] as $handler) {
			switch ($handler) {
				// File based sessions
				case self::HANDLER_FILE:
					$this->_handlers[self::HANDLER_FILE] = new Session\File(
						$this->_app->getResource(self::RES_FILE),
						isset($this->_config[self::CONF_FILE]) ? $this->_config[self::CONF_FILE] : array()
					);
					break;
				// Memcache driven sessions
				case self::HANDLER_MEMCACHE:
					$this->_handlers[self::HANDLER_MEMCACHE] = new Session\Memcache(
						$this->_app->getResource(self::RES_MEMCACHE),
						isset($this->_config[self::CONF_MEMCACHE]) ? $this->_config[self::CONF_MEMCACHE] : array()
					);
					break;
				// Database driven sessions
				case self::HANDLER_DATABASE:
					$this->_handlers[self::HANDLER_DATABASE] = new Session\Database(
						$this->_app->getResource(self::RES_DATABASE),
						isset($this->_config[self::CONF_DATABASE]) ?
							$this->_config[self::CONF_DATABASE] : array()
					);
					break;
			}
		}

		// Set expriry
		$this->_expires = isset($this->_config[self::CONF_EXPIRES]) ? $this->_config[self::CONF_EXPIRES] : self::SESSION_EXPIRY;

		// Register handler
		$this->register();

		// Start session
		session_start();
	}

	/**
	 * Register session handler
	 *
	 * @return void
	 */
	public function register()
	{
		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'gc')
		);
	}

	/**
	 * Open session
	 *
	 * @param string $path
	 * @param string $name
	 * @return void
	 */
	public function open($path, $name)
	{
		// Not needed
	}

	/**
	 * Close session (unused)
	 *
	 * @return void
	 */
	public function close()
	{
		// Not needed
	}

	/**
	 * Read session data
	 *
	 * @param string $id
	 * @return string
	 */
	public function read($id)
	{
		// Read handlers until some data is returned
		foreach ($this->_handlers as $handler) {
			$data = $handler->read($id);
			if ($data !== false) {
				return $data;
			}
		}
		return '';
	}

	/**
	 * Write session data
	 *
	 * @param string $id
	 * @param string $data
	 * @return void
	 */
	public function write($id, $data)
	{
		// Write session to all handlers
		foreach ($this->_handlers as $handler) {
			$handler->write($id, $data, time() + $this->_expires);
		}
	}

	/**
	 * Destroy session data
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{
		// Destroy session for all handlers
		foreach ($this->_handlers as $handler) {
			$handler->destroy($id);
		}
	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc()
	{
		// Garbage collect all handlers
		foreach ($this->_handlers as $handler) {
			$handler->gc();
		}
	}

	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		// Fixes PHP bug
		session_write_close();
	}

}