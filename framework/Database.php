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
use LiteMVC\Database as Database;

class Database
{

	/**
	 * Database config
	 * 
	 * @var array
	 */
	protected $_config;
	
	/**
	 * An array of open connections
	 * 
	 * @var array
	 */
	protected $_connections = array();

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_CONFIG = 'Config';

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_DRIVER = 'driver';
	const CONF_HOST = 'host';
	const CONF_USER = 'username';
	const CONF_PASS = 'password';
	const CONF_DB = 'database';
	const CONF_NOERR = 'noerrors';
	
	/**
	 * Constructor
	 * 
	 * @param App $app
	 * @return void
	 */
	public function  __construct(App $app) {
		// Check config
		$config = $app->getResource(self::RES_CONFIG)->database;
		if (!is_null($config)) {
			$this->_config = $config;
		} else {
			throw new Database\Exception('No database configuration has been specified.');
		}
	}

	/**
	 * Get database connection
	 *
	 * @param string $name
	 * @return Database\Connection
	 */
	public function getConnection($name)
	{
		// If connection exists return it
		if (!isset($this->_connections[$name])) {
			// Check if configuration exists
			if (array_key_exists($name, $this->_config)) {
				// Config check
				if (!isset($this->_config[$name][self::CONF_DRIVER], $this->_config[$name][self::CONF_HOST],
						$this->_config[$name][self::CONF_USER], $this->_config[$name][self::CONF_PASS])) {
					throw new Database\Exception('Configuration for database \'' . $name . '\' is invalid.');
				}

				// Instantiate new connection
				$class = 'LiteMVC\\Database\\' . $this->_config[$name][self::CONF_DRIVER];
				$this->_connections[$name] = new $class(
					$this->_config[$name][self::CONF_HOST],
					$this->_config[$name][self::CONF_USER],
					$this->_config[$name][self::CONF_PASS],
					isset($this->_config[$name][self::CONF_DB]) ? $this->_config[$name][self::CONF_DB] : null,
					isset($this->_config[$name][self::CONF_NOERR]) ? $this->_config[$name][self::CONF_NOERR] : null
				);
			} else {
				throw new Database\Exception('Database \'' . $name . '\' is not defined in the configuration.');
			}
		}
		return $this->_connections[$name];
	}

	/**
	 * Set new database connection
	 *
	 * @param string $name
	 * @param Database\Connection $object
	 * @return void
	 */
	public function setConnection($name, $object)
	{
		$this->_connections[$name] = $object;
	}

}