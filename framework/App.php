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
	protected $_resources = array();

	/**
	 * File paths
	 *
	 * @var string
	 */
	const Path_Config = '/configs/';
	const Path_Cache  = '/cache/';
	
	/**
	 * Cache settings
	 * 
	 * @var string
	 */
	const Cache_Prefix = 'LiteMVC';
	const Cache_Config = 'Config';
	
	/**
	 * Cache lifetime
	 * 
	 * @var int
	 */
	const CacheLifetime_Config = 86400;
	
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
	 * Check if an application resource exists
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function isResource($name)
	{
		if (isset($this->_resources[$name]) && is_object($this->_resources[$name])) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get an application resource
	 * 
	 * @param string $name
	 * @return object
	 */
	public function getResource($name)
	{
		if (isset($this->_resources[$name]) || $this->loadResource($name)) {
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
	 * Load an application resource
	 * 
	 * @param string $name
	 * @param Config $config
	 * @return bool
	 */
	public function loadResource($name)
	{
		// Attempt to load a class from the specified name
		$class = 'LiteMVC\\' . $name;
		if (class_exists($class)) {
			$obj = new $class($this);
			$this->setResource($name, $obj);
			return true;
		}
		return false;
	}
	
	/**
	 * Initialise the applicatoin
	 *
	 * @param string $configFile
	 * @param Memcache $cache
	 */
	public function init($configFile, Cache\Memcache $cache = null)
	{
		// If no memcache use a file cache
		if (is_null($cache)) {
			$cache = $this->getResource('Cache\File');
		} else {
			$this->setResource('Cache\Memcache', $cache);
		}
		// Check modification time of config file
		$fmt = filemtime(\PATH . self::Path_Config . $configFile);
		// Load configuration
		$config = $cache->get(self::Cache_Prefix . '_' . self::Cache_Config);
		// Check config is valid and recent
		if ($config === false || $config['fmt'] < $fmt) {
			// Reload config from ini
			$config['fmt'] = $fmt;
			$config['obj'] = new App\Config\Ini(\PATH . self::Path_Config . $configFile, \ENVIRONMENT);
			// Update cache
			$cache->set(self::Cache_Prefix . '_' . self::Cache_Config, $config, 0, self::CacheLifetime_Config);
		}
		// Save application resources
		$this->setResource('Config', $config['obj']);
		// Load resources from config
		if (!is_null($config['obj']->init)) {
			$init = $config['obj']->init;
			if (is_array($init['load']) && count($init['load'])) {
				foreach ($init['load'] as $resource) {
					$this->loadResource($resource);
				}
			}
		}
		// Start session
		session_start();
	}
	
}