<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC;

// Namespace aliases
use LiteMVC\App as App;

// Require resource and autoloader classes
require_once 'Autoload.php';
require_once 'Resource.php';

class App extends Resource {

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
	const PATH_APP		= '/apps/';
	const PATH_CACHE	= '/cache/';
	const PATH_CONFIG	= '/configs/';

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_LOAD	= 'load';
	const CONF_SKIP	= 'skip';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Setup the autoloader
		$loader = new Autoload();
		$loader->register();

		// Save autoloader as an application resource
		$this->setResource(self::RES_LOADER, $loader);
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
	 * Unset an application resource
	 *
	 * @param string $name
	 * @return void
	 */
	public function unsetResource($name)
	{
		unset($this->_resources[$name]);
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
	 * Load an application resource
	 *
	 * @param string $name
	 * @param mixed $params
=	 * @return bool
	 */
	public function loadResource($name, $params = null)
	{
		// Attempt to load a class from the specified name
		$class = __NAMESPACE__ . '\\' . $name;
		if (is_null($params)) {
			$obj = new $class($this);
		} else {
			$obj = new $class($params);
		}
		if ($obj instanceof Resource\Loadable) {
			$obj->init();
		}
		$this->setResource($name, $obj);
		return true;
	}

	/**
	 * Unload an application resource
	 *
	 * @param string $name
	 * @return bool
	 */
	public function unloadResource($name)
	{
		if ($this->getResource($name)) {
			$this->unsetResource($name);
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
	public function init($configFile, $cacheModule = self::RES_FILE, $cacheParams = null)
	{
		// Load cache module
		$cache = $this->getResource($cacheModule, is_null($cacheParams) ? \PATH . self::PATH_CACHE : $cacheParams);

		// Load configuration
		$config = $this->getResource(self::RES_CONF_INI);
		$config->setCache($cache);
		$config->load(\PATH . self::PATH_CONFIG . $configFile, \ENVIRONMENT);

		// Config is special case and is saved as 'Config' resource for convenience
		$this->setResource(self::RES_CONFIG, $config);

		// Configure autoloader
		if (!is_null($config->autoload)) {
			$autoload = $this->getResource(self::RES_LOADER);
			foreach ($config->autoload as $ns => $path) {
				$autoload->setPath($ns, \PATH . $path);
			}
		}

		// Load and initialise modules
		$init = $config->init ? $config->init : array();
		if (isset($init[self::CONF_LOAD]) && is_array($init[self::CONF_LOAD])) {
			foreach ($init[self::CONF_LOAD] as $resource) {
				$resource = $this->getResource($resource);
			}
		}

		// If request module is loaded initialise other resources
		if ($this->isResource(self::RES_REQUEST)) {
			$req = $this->getResource(self::RES_REQUEST);
			if (isset($init[$req->getModule()])) {
				$this->initRequest($req, $init[$req->getModule()]);
			}
		}

		return $this;
	}

	/**
	 * Initialise request based resources
	 *
	 * @param Request $req
	 * @param array $init
	 */
	public function initRequest(Request $req, array $init)
	{
		// Module/controller specific resources
		if (isset($init[$req->getController()][self::CONF_LOAD]) && is_array($init[$req->getController()][self::CONF_LOAD])) {
			foreach ($init[$req->getController()][self::CONF_LOAD] as $resource) {
				$this->loadResource($resource);
			}
		}

		// Allow a controller specific setting to skip main module loading
		$skip = array();
		if (isset($init[$req->getController()][self::CONF_SKIP]) && is_array($init[$req->getController()][self::CONF_SKIP])) {
			foreach ($init[$req->getController()][self::CONF_SKIP] as $resource) {
				$skip[] = $resource;
			}
		}

		// Module specific resources
		if (isset($init[self::CONF_LOAD]) && is_array($init[self::CONF_LOAD])) {
			foreach ($init[self::CONF_LOAD] as $resource) {
				if (!in_array($resource, $skip)) {
					$this->loadResource($resource);
				}
			}
		}
	}

	/**
	 * Run application
	 *
	 * @return void
	 */
	public function run() {
		// Dispatch request
		if ($this->isResource(self::RES_REQUEST)) {
			$this->getResource(self::RES_DISPATCH)->dispatch();
		}

		// Page output
		$output = false;
		if ($this->isResource(self::RES_HTML)) {
			$output = $this->getResource(self::RES_HTML);
		} elseif ($this->isResource(self::RES_JSON)) {
			$output = $this->getResource(self::RES_JSON);
		}
		if ($output instanceof View) {
			$output->render();
			echo $output;
		}
	}

}