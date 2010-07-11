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
	 * Constructor
	 * 
	 * @param App $app 
	 * @return void
	 */
	public function  __construct(App $app) {
		// Check config
		$config = $app->getResource('Config')->Database;
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
				$class = 'LiteMVC\\Database\\' . $this->_config[$name]['driver'];
				$this->_connections[$name] = new $class(
					$this->_config[$name]['host'],
					$this->_config[$name]['username'],
					$this->_config[$name]['password'],
					$this->_config[$name]['database'],
					$this->_config[$name]['noerrors']
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