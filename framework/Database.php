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
use LiteMVC\Database as Database;

class Database
{

	/**
	 * Database config
	 * 
	 * @var App\Config
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
		$config = $app->getResource('Config');
		if ($config->Database instanceof App\Config) {
			$this->_config = $config->Database;
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
		if (isset($this->_connections[$name])) {
			return $this->_connections[$name];
		}
		// Check if configuration exists
		if ($this->_config->$name instanceof App\Config) {
			$this->_connections[$name] = new Database\Connection(
				$this->_config->$name->host,
				$this->_config->$name->username,
				$this->_config->$name->password,
				$this->_config->$name->database
			);
		}
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