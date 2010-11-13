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

// Namespace aliases
use LiteMVC\Session as Session;

class Session
{

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
	const CONFIG_HANDLER = 'handler';
	const CONFIG_EXPIRES = 'expires';
	const CONFIG_FILE = 'file';
	const CONFIG_MEMCACHE = 'memcache';
	const CONFIG_DATABASE = 'database';

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
	const RESOURCE_FILE = 'Cache\File';
	const RESOURCE_MEMCACHE = 'Cache\Memcache';
	const RESOURCE_DATABASE = 'Database';

	/**
	 * Default session length
	 *
	 * @var int
	 */
	const SESSION_EXPIRY = 3600;

	/**
	 * Constructor
	 * 
	 * @param App $app 
	 * @return void
	 */
	public function  __construct(App $app) {
		// Check config
		$config = $app->getResource('Config')->session;
		if (is_null($config)) {
			throw new Session\Exception('No session configuration has been specified.');
		}
		// Load handlers
		if (isset($config[self::CONFIG_HANDLER])) {
			foreach ($config[self::CONFIG_HANDLER] as $handler) {
				switch ($handler) {
					// File based sessions
					case self::HANDLER_FILE:
						$this->_handlers[self::HANDLER_FILE] = new Session\File(
							$app->getResource(self::RESOURCE_FILE),
							isset($config[self::CONFIG_FILE]) ?
								$config[self::CONFIG_FILE] : array()
						);
						break;
					// Memcache driven sessions
					case self::HANDLER_MEMCACHE:
						$this->_handlers[self::HANDLER_MEMCACHE] = new Session\Memcache(
							$app->getResource(self::RESOURCE_MEMCACHE),
							isset($config[self::CONFIG_MEMCACHE]) ?
								$config[self::CONFIG_MEMCACHE] : array()
						);
						break;
					// Database driven sessions
					case self::HANDLER_DATABASE:
						$this->_handlers[self::HANDLER_DATABASE] = new Session\Database(
							$app->getResource(self::RESOURCE_DATABASE),
							isset($config[self::CONFIG_DATABASE]) ?
								$config[self::CONFIG_DATABASE] : array()
						);
						break;
				}
			}
		} else {
			throw new Session\Exception('No session handlers specified in configuration.');
		}
		// Set expriry
		$this->_expires = isset($config[self::CONFIG_EXPIRES]) ?
			$config[self::CONFIG_EXPIRES] : self::SESSION_EXPIRY;
		// Register handler
		$this->register();
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
	public function open($path, $name) {}

	/**
	 * Close session (unused)
	 *
	 * @return void
	 */
	public function close() {}

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