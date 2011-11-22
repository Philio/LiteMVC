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
	 * Exception object pushed from dispatch
	 *
	 * @var Exception 
	 */
	protected $_exception;

	/**
	 * Plugin objects
	 *
	 * @var array
	 */
	protected $_plugins = array();

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_REQUEST	= 'Request';
	const RES_HTML		= 'View\HTML';
	const RES_JSON		= 'View\JSON';
	const RES_DATABASE	= 'Database';

	/**
	 * Default namespace prefix
	 *
	 * @var string
	 */
	const NS_PREFIX = 'LiteMVC';

	/**
	 * Namespace body for plugins
	 *
	 * @var string
	 */
	const NS_BODY = '\\Controller\\Plugin\\';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @param string $controller
	 * @param string $action
	 */
	public function __construct(App $app, $controller, $action, $exception = null)
	{
		// Reference App object for resource loading
		$this->_app = $app;

		// Setup request
		$this->_request = $this->getResource(self::RES_REQUEST);

		// Setup HTML view
		if ($this->isResource(self::RES_HTML)) {
			$this->_view = $this->getResource(self::RES_HTML);
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
			// Set exception
			if (!is_null($exception)) {
				$this->_view->exception = $exception;
			}

		// Setup JSON view
		} elseif ($app->isResource(self::RES_JSON)) {
			$this->_view = $app->getResource(self::RES_JSON);
			// Set exception
			if (!is_null($exception)) {
				$this->_view->exception = $exception;
			}
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
			$class = self::NS_PREFIX . self::NS_BODY . ucfirst($name);
			if (!class_exists($class)) {
				echo $class = $this->_request->getModule() . self::NS_BODY . ucfirst($name);
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
		$db = $this->getResource(self::RES_DATABASE);
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

	/**
	 * Get a new instance of a form
	 *
	 * @param string $name
	 * @return object
	 */
	public function getForm($name)
	{
		$frm = new $name($this->_app);
		return $frm;
	}

}
