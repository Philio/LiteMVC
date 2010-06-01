<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
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
	 * Constructor
	 * 
	 * @param App $app 
	 * @return void
	 */
	public function  __construct(App $app) {
		// Check config
		$config = $app->getResource('Config');
		if ($config->Session instanceof App\Config) {
			$sessConfig = $config->Session;
		} else {
			throw new Session\Exception('No session configuration has been specified.');
		}
		// Load handlers
		if ($sessConfig->handler) {
			foreach ($sessConfig->handler->toArray() as $handler) {
				switch ($handler) {
					case 'Memcache':
						$this->_handlers['Memcache'] = new Session\Memcache($app->getResource('Cache\Memcache'));
						break;
					case 'Database':
						$this->_handlers['Database'] = new Session\Database($app->getResource('Database')->getConnection('Session'), $sessConfig->Database);
						break;
				}
			}
		} else {
			throw new Session\Exception('No session handlers specified in configuration.');
		}
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
	public function open($path, $name)
	{

	}

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

	}

	/**
	 * Destroy session data
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{

	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc()
	{

	}

}