<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
 */
namespace LiteMVC;

// Namespace aliases
use LiteMVC\Database as Database;

class Database extends Resource
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
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_DRIVER	= 'driver';
	const CONF_HOST		= 'host';
	const CONF_USER		= 'username';
	const CONF_PASS		= 'password';
	const CONF_DB		= 'database';
	const CONF_NOERR	= 'noerrors';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function  __construct(App $app) {
		// Set config
		$this->_config = $app->getResource(self::RES_CONFIG)->database;
	}

	/**
	 * Get database connection
	 *
	 * @param string $name
	 * @return Database\Connection
	 */
	public function getConnection($name)
	{
		// Check config
		if (is_null($this->_config)) {
			throw new Database\Exception('No database configuration has been specified.');
		}

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