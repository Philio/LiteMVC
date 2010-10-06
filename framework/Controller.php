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

abstract class Controller
{

	/**
	 * App object
	 *
	 * @var App
	 */
	protected $_app;

	/**
	 * Request object
	 *
	 * @var Request
	 */
	protected $_request;

	/**
	 * View object
	 *
	 * @var View\HTML | View\JSON
	 */
	protected $_view;

	/**
	 * Plugin objects
	 *
	 * @var array
	 */
	protected $_plugins = array();

	/**
	 * Default namespace prefix
	 *
	 * @var stirng
	 */
	const NAMESPACE_PREFIX = 'LiteMVC';

	/**
	 * Namespace body for plugins
	 *
	 * @var string
	 */
	const NAMESPACE_BODY = '\\Controller\\Plugin\\';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct(App $app, $controller, $action)
	{
		// Reference App object for resource loading
		$this->_app = $app;
		// Setup request
		$this->_request = $this->getResource('Request');
		// Setup HTML view
		if ($this->isResource('View\HTML')) {
			$this->_view = $this->getResource('View\HTML');
			// Set module
			$this->_view->setModule($this->_request->getModule());
			// Set layout
			$this->_view->setLayout($this->_request->getLayout($controller));
			// Set page
			$parts = explode('-', $action);
			$action = '';
			foreach ($parts as $part) {
				$action .= ucfirst($part);
			}
			$this->_view->setPage(ucfirst($controller) . '/' . ucfirst($action));
			// Set path
			$this->_view->path = $this->_request->getRelativePath();
		// Setup JSON view
		} elseif ($app->isResource('View\JSON')) {
			$this->_view = $app->getResource('View\JSON');
		}
	}

	/**
	 * Call magic method
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public function __call($name, $args) {
		// Check if plugin is instanciated
		if (!isset($this->_plugins[$name])) {
			// Check if class exists within framework namespace or app namespace
			$class = self::NAMESPACE_PREFIX . self::NAMESPACE_BODY . ucfirst($name);
			if (!class_exists($class)) {
				echo $class = $this->_request->getModule() . self::NAMESPACE_BODY . ucfirst($name);
				if (!class_exists($class)) {
					return false;
				}
			}
			// Call class
			$this->_plugins[$name] = new $class();
		}
		// Return function result or class if no process function
		if (is_callable(array($this->_plugins[$name], 'process'))) {
			return call_user_func_array(array($this->_plugins[$name], 'process'), $args);
		}
		return $this->_plugins[$name];
	}

	/**
	 * Check if an application resource exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isResource($name)
	{
		if ($this->_app instanceof App) {
			return $this->_app->isResource($name);
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
		if ($this->_app instanceof App) {
			return $this->_app->getResource($name, $params);
		}
		return null;
	}

	/**
	 * Get a new instance of a model
	 *
	 * @param string $name
	 * @param string $cache
	 * @return mixed
	 */
	public function getModel($name, $cache = null, $lifetime = null)
	{
		$db = $this->getResource('Database');
		if ($db instanceof Database) {
			$model = new $name($db);
			if (!is_null($cache) && !is_null($lifetime)) {
				$model->setCache($this->getResource($cache));
				$model->setCacheLifetime($lifetime);
			}
			return $model;
		}
		return null;
	}

}
