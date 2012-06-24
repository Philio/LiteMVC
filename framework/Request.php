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

class Request extends Resource\Loadable
{

	/**
	 * Config data
	 *
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Application path
	 *
	 * @var string
	 */
	protected $_appPath;

	/**
	 * Relative path to docroot
	 *
	 * @var string
	 */
	protected $_relativePath;

	/**
	 * URI relative to framework root
	 *
	 * @var string
	 */
	protected $_uri;

	/**
	 * Module requested
	 *
	 * @var string
	 */
	protected $_module;

	/**
	 * Controller requested
	 *
	 * @var string
	 */
	protected $_controller;

	/**
	 * Action requested
	 *
	 * @var string
	 */
	protected $_action;

	/**
	 * Params
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Check config
		$config = $app->getResource('Config')->request;
		if (!is_null($config)) {
			$this->_config = $config;
		}

		// Set app path
		$this->_appPath = $app::PATH_APP;
	}

	/**
	 * Init request
	 *
	 * @return void
	 */
	public function init()
	{
		// Check that server variables exist
		$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null;
		$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;

		// Determine script path relative to doc root
		// Allows for flexible deployment
		// e.g. framework root could reside at example.com/a/b/c/d/
		$this->_relativePath = substr($scriptName, 0 , strrpos($scriptName, '/'));

		// Determine URI relative to framework root
		$this->_uri = str_replace($this->_relativePath, '', $requestUri);
		$uri = $this->_uri;

		// Ignore query strings
		if (strpos($uri, '?') !== false) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}

		// Ignore bookmarks
		if (strpos($uri, '#') !== false) {
			$uri = substr($uri, 0, strpos($uri, '#'));
		}

		// Routing
		if (!isset($this->_config['router']) || !$this->_parseRoute($uri, $this->_config['router'])) {
			$this->_defaultRouting($uri);
		}
	}

	/**
	 * Handle routing based on supplied regex pattern
	 *
	 * @param string $uri
	 * @param string | array $patterns
	 */
	protected function _parseRoute($uri, $patterns)
	{
		// Make sure pattern is an array
		if (!is_array($patterns)) {
			$patterns = array($patterns);
		}

		// Try and match pattern
		$matches = array();
		foreach ($patterns as $pattern) {
			preg_match('/' . $pattern . '/', $uri, $matches);
			if (count($matches)) {
				break;
			}
			if ($pattern == end($patterns)) {
				return false;
			}
		}

		// Determine module
		if (isset($matches['module'])) {
			$this->_module = $matches['module'];
		} elseif (isset($this->_config['default']['module'])) {
			$this->_module = $this->_config['default']['module'];
		} else {
			throw new App\Exception('Unable to determine which module to load, no default module specified in config.');
		}

		// Determine controller
		if (isset($matches['controller'])) {
			$this->_controller = $matches['controller'];
		} elseif (isset($this->_config[$this->_module]['default']['controller'])) {
			$this->_controller = $this->_config[$this->_module]['default']['controller'];
		} else {
			throw new App\Exception('Unable to determine controller, no default specified in config for module ' . $this->_module . '.');
		}

		// Determine action
		if (isset($matches['action'])) {
			$this->_action = $matches['action'];
		} elseif (isset($this->_config[$this->_module]['default']['action'])) {
			$this->_action = $this->_config[$this->_module]['default']['action'];
		} else {
			throw new App\Exception('Unable to determine action, no default specified in config for module ' . $this->_module . '.');
		}

		// Process any params
		if (isset($matches['params'])) {
			$parts = explode('/', $matches['params']);
			$key = null;
			foreach ($parts as $value) {
				if (is_null($key)) {
					$key = $value;
				} else {
					$this->_params[$key] = $value;
					$key = null;
				}
			}
		}

		// Check for custom params
		foreach ($matches as $key => $value) {
			// Ignore predefined or integer keys
			if (in_array($key, array('module', 'controller', 'action', 'params')) || is_numeric($key)) {
				continue;
			}
			$this->_params[$key] = $value;
		}
		return true;
	}

	/**
	 * Default routing handles typical module/controller/action/params routes
	 *
	 * @param string $uri
	 * @throws App\Exception
	 */
	protected function _defaultRouting($uri)
	{
		// Trim leading or trailing /
		$uri = trim($uri, '/');

		// Split up URI
		$parts = explode('/', $uri);

		// Array index to check
		$index = 0;

		// Determine module
		if (isset($parts[$index]) && !empty($parts[$index]) && file_exists(\PATH . $this->_appPath . $parts[0])) {
			$this->_module = $parts[$index];
			unset($parts[$index]);
			$index ++;
		} elseif (isset($this->_config['default']['module'])) {
			$this->_module = $this->_config['default']['module'];
		} else {
			throw new App\Exception('Unable to determine which module to load, no default module specified in config.');
		}

		// Determine controller
		if (isset($parts[$index]) && !empty($parts[$index])) {
			$this->_controller = $parts[$index];
			unset($parts[$index]);
			$index ++;
		} elseif (isset($this->_config[$this->_module]['default']['controller'])) {
			$this->_controller = $this->_config[$this->_module]['default']['controller'];
		} else {
			throw new App\Exception('Unable to determine controller, no default specified in config for module ' . $this->_module . '.');
		}

		// Determine action
		if (isset($parts[$index]) && !empty($parts[$index])) {
			$this->_action = $parts[$index];
			unset($parts[$index]);
			$index ++;
		} elseif (isset($this->_config[$this->_module]['default']['action'])) {
			$this->_action = $this->_config[$this->_module]['default']['action'];
		} else {
			throw new App\Exception('Unable to determine action, no default specified in config for module ' . $this->_module . '.');
		}

		// Get any params
		if (count($parts)) {
			$key = null;
			foreach ($parts as $value) {
				if (is_null($key)) {
					$key = $value;
				} else {
					$this->_params[$key] = $value;
					$key = null;
				}
			}
		}
	}

	/**
	 * Get relative path of web root
	 *
	 * @return string
	 */
	public function getRelativePath()
	{
		return $this->_relativePath;
	}

	/**
	 * Get module name
	 *
	 * @return string
	 */
	public function getModule()
	{
		return $this->_module;
	}

	/**
	 * Get controller name
	 *
	 * @return string
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * Get action name
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->_action;
	}

	/**
	 * Get layout
	 *
	 * @param string $controller
	 * @return string
	 */
	public function getLayout($controller)
	{
		if (isset($this->_config[$this->_module][$controller]['layout'])) {
			return ucfirst($this->_config[$this->_module][$controller]['layout']);
		} elseif (isset($this->_config[$this->_module]['default']['layout'])) {
			return ucfirst($this->_config[$this->_module]['default']['layout']);
		}
		return null;
	}

	/**
	 * Return a section of the config if it exists
	 *
	 * @param string $section
	 * @return array
	 */
	public function getConfig($section)
	{
		if (isset($this->_config[$this->_module][$section])) {
			return $this->_config[$this->_module][$section];
		}
		return null;
	}

	/**
	 * Check if request was a GET
	 *
	 * @return bool
	 */
	public function isGet()
	{
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}

	/**
	 * Check if request was a POST
	 *
	 * @return bool
	 */
	public function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}

	/**
	 * Check if request was a PUT
	 *
	 * @return bool
	 */
	public function isPut()
	{
		return $_SERVER['REQUEST_METHOD'] == 'PUT';
	}

	/**
	 * Check if request was a DELETE
	 *
	 * @return bool
	 */
	public function isDelete()
	{
		return $_SERVER['REQUEST_METHOD'] == 'DELETE';
	}

	/**
	 * Check if request was AJAX
	 *
	 * @return bool
	 */
	public function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	/**
	 * Get a param from URI
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getParam($key)
	{
		if (isset($this->_params[$key])) {
			return $this->filterValue($this->_params[$key]);
		} elseif (isset($_REQUEST[$key])) {
			return $this->filterValue($_REQUEST[$key]);
		}
		return null;
	}

	/**
	 * Get a post param
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getPostParam($key)
	{
		if (isset($_POST[$key])) {
			return $this->filterValue($_POST[$key]);
		}
		return null;
	}

	/**
	 * Get all post params
	 *
	 * @return array
	 */
	public function getAllPostParams()
	{
		return $this->filterValue($_POST);
	}

	/**
	 * Filter a value or array of values
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function filterValue($value) {
		if (is_array($value)) {
			foreach ($value as $arrKey => $arrValue) {
				$value[$arrKey] = $this->filterValue($arrValue);
			}
		} elseif (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			return stripslashes($value);
		}
		return $value;
	}

	/**
	 * Redirect request
	 *
	 * @param string $action
	 * @param string $controller
	 * @param string $module
	 * @return void
	 */
	public function redirect($action = null, $controller = null, $module = null, $params = null, $return = false)
	{
		// Replace null values with current values
		$module = is_null($module) ? $this->_module : $module;
		$controller = is_null($controller) ? $this->_controller : $controller;
		$action  = is_null($action) ? $this->_action : $action;

		// Redirect to new uri
		$uri = $this->_relativePath . '/';
		if (isset($this->_config['default']['module']) && $module != $this->_config['default']['module']) {
			$uri .= $module . '/';
		}
		$uri .= $controller;
		if (!is_null($params) || (isset($this->_config['default']['action']) && $action != $this->_config['default']['module'])) {
			$uri .= '/' . $action;
		}
		if (!is_null($params)) {
			foreach ($params as $key => $value) {
				$uri .= '/' . $key . '/' . $value;
			}
		}
		if ($return) {
			return $uri;
		}
		header('Location: ' . $uri);
		exit;
	}

}