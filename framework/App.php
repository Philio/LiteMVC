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
	const PATH_APP    = '/apps/';
	const PATH_CACHE  = '/cache/';
	const PATH_CONFIG = '/configs/';

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONFIG_PRELOAD = 'preload';
	const CONFIG_LOAD = 'load';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RESOURCE_FILE = 'Cache\File';
	const RESOURCE_CONFIG = 'Config';
	const RESOURCE_CONFIG_INI = 'App\Config\Ini';
	const RESOURCE_LOADER = 'Autoload';
	const RESOURCE_REQUEST = 'Request';
	const RESOURCE_DISPATCH = 'Dispatcher';
	const RESOURCE_HTML = 'View\HTML';
	const RESOURCE_JSON = 'View\JSON';
	
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
		$this->setResource(self::RESOURCE_LOADER, $loader);
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
	public function init($configFile, $cacheModule = self::RESOURCE_FILE, $cacheParams = null)
	{
		// Load cache module
		$cache = $this->getResource($cacheModule, is_null($cacheParams) ?
				\PATH . self::PATH_CACHE : $cacheParams);
		// Load configuration
		$config = $this->getResource(self::RESOURCE_CONFIG_INI);
		$config->setCache($cache);
		$config->load(\PATH . self::PATH_CONFIG . $configFile, \ENVIRONMENT);
		// Config is special case and is saved as 'Config' resource for convenience
		$this->setResource(self::RESOURCE_CONFIG, $config);
		// Configure autoloader
		if (!is_null($config->autoload)) {
			$autoload = $this->getResource(self::RESOURCE_LOADER);
			foreach ($config->autoload as $ns => $path) {
				$autoload->setPath($ns, \PATH . $path);
			}
		}
		// Preload modules
		if (!is_null($config->init)) {
			$init = $config->init;
			if (isset($init[self::CONFIG_PRELOAD]) &&
					is_array($init[self::CONFIG_PRELOAD])) {
				foreach ($init[self::CONFIG_PRELOAD] as $resource) {
					$this->loadResource($resource);
				}
			}
		}
		// Start session
		session_start();
		// Get request
		$req = $this->getResource(self::RESOURCE_REQUEST);
		$req->process();
		// Load other resources
		if (!is_null($config->init)) {
			$init = $config->init;
			// Application specific resources
			if (isset($init[self::CONFIG_LOAD]) &&
					is_array($init[self::CONFIG_LOAD])) {
				foreach ($init[self::CONFIG_LOAD] as $resource) {
					$this->loadResource($resource);
				}
			}
			// Module specific resources
			if (isset($init[$req->getModule()][self::CONFIG_LOAD]) &&
					is_array($init[$req->getModule()][self::CONFIG_LOAD])) {
				foreach ($init[$req->getModule()][self::CONFIG_LOAD] as $resource) {
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
		$this->getResource(self::RESOURCE_DISPATCH)->dispatch();
		// Page output
		$output = false;
		if ($this->isResource(self::RESOURCE_HTML)) {
			$output = $this->getResource(self::RESOURCE_HTML);
		} elseif ($this->isResource(self::RESOURCE_JSON)) {
			$output = $this->getResource(self::RESOURCE_JSON);
		}
		if ($output) {
			$output->render();
			echo $output;
		}
	}
	
}