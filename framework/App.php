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

// Require autoloader class
require_once 'App/Autoload.php';

// Namespace aliases
use LiteMVC\App as App;

class App {
	
	/**
	 * Resources
	 * 
	 * @var array
	 */
	private $_resources = array();

	/**
	 * File paths
	 *
	 * @var string
	 */
	const Path_Config = '/configs/';
	
	/**
	 * Get an application resource
	 * 
	 * @param string $name
	 * @return object
	 */
	public function getResource($name)
	{
		if (isset($this->_resources[$name])) {
			return $this->_resources[$name];
		}
		return false;
	}

	/**
	 * Set an application resource
	 * 
	 * @param string $name
	 * @param object $object
	 * @return void
	 */
	public function setResource($name, $object)
	{
		$this->_resources[$name] = $object;
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct()
	{
		// Setup the autoloader
		$loader = new App\Autoload();
		$loader->register();
		$loader->setPath(__NAMESPACE__, \PATH . '/framework');
		// Save autoloader as an application resource
		$this->setResource('Autoloader', $loader);
	}

	/**
	 * Initialise the applicatoin
	 *
	 * @param string $configFile
	 * @param Memcache $cache
	 */
	public function init($configFile, LiteMVC\Memcache $cache = null)
	{
		$config = new App\Config\Ini(\PATH . self::Path_Config . $configFile, \ENVIRONMENT);
	}
	
}
