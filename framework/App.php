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
	const Path_App    = '/apps/';
	const Path_Cache  = '/cache/';
	const Path_Config = '/configs/';
	
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
		$this->setResource('Autoload', $loader);
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
	 * @param mixed $params
	 * @return object
	 */
	public function getResource($name, $params = null)
	{
		if (isset($this->_resources[$name]) || $this->loadResource($name, $params)) {
			return $this->_resources[$name];
		}
		return null;
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
	 * @param mixed $params
=	 * @return bool
	 */
	public function loadResource($name, $params = null)
	{
		// Attempt to load a class from the specified name
		$class = 'LiteMVC\\' . $name;
		if (class_exists(($class))) {
			if (is_null($params)) {
				$obj = new $class($this);
			} else {
				$obj = new $class($params);
			}
			$this->setResource($name, $obj);
			return true;
		}
		return false;
	}
	
	/**
	 * Initialise the applicatoin
	 *
	 * @param string $configFile
	 * @param string $cacheModule
	 * @param mixed $cacheParams
	 * @return App
	 */
	public function init($configFile, $cacheModule = 'Cache\File', $cacheParams = null)
	{
		// Load cache module
		$cache = $this->getResource($cacheModule, is_null($cacheParams) ? \PATH . self::Path_Cache : $cacheParams);
		// Load configuration
		$config = $this->getResource('App\Config\Ini');
		$config->setCache($cache);
		$config->load(\PATH . self::Path_Config . $configFile, \ENVIRONMENT);
		// Config is special case and is saved as 'Config' resource for convenience
		$this->setResource('Config', $config);
		// Configure autoloader
		if (!is_null($config->Autoload)) {
			$autoload = $this->getResource('Autoload');
			foreach ($config->Autoload as $ns => $path) {
				$autoload->setPath($ns, \PATH . $path);
			}
		}
		// Preload modules
		if (!is_null($config->Init)) {
			$init = $config->Init;
			if (is_array($init['preload']) && count($init['preload'])) {
				foreach ($init['preload'] as $resource) {
					$this->loadResource($resource);
				}
			}
		}
		// Start session
		session_start();
		// Get request
		$req = $this->getResource('Request');
		$req->process();
		// Load other modules
		if (!is_null($config->Init)) {
			$init = $config->Init;
			if (is_array($init['load']) && count($init['load'])) {
				foreach ($init['load'] as $resource) {
					$this->loadResource($resource);
				}
			}
		}
		return $this;
	}

	/**
	 * Run application
	 *
	 * @return void
	 */
	public function run() {
		// Dispatch request
		$this->getResource('Dispatcher')->dispatch();
		// Page output
		if ($this->isResource('View\HTML')) {
			$output = $this->getResource('View\HTML');
			$output->render();
			echo $output;
		}
	}
	
}