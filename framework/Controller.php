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
	private $_app;

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

	public function __construct(App $app, $controller, $action)
	{
		// Reference App object for resource loading
		$this->_app = $app;
		// Setup request/view
		$this->_request = $this->getResource('Request');
		if ($this->isResource('View\HTML')) {
			$this->_view = $this->getResource('View\HTML');
			// Set module
			$module = $this->_request->getModule();
			$this->_view->setModule($module);
			// Set layout
			$this->_view->setLayout($this->_request->getLayout($controller));
			// Set page
			$parts = explode('-', $action);
			$action = '';
			foreach ($parts as $part) {
				$action .= ucfirst($part);
			}
			$this->_view->setPage(ucfirst($controller) . '/' . ucfirst($action));
		} elseif ($app->isResource('View\JSON')) {
			$this->_view = $app->getResource('View\JSON');
		}
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
	 * @return mixed
	 */
	public function getModel($name)
	{
		$db = $this->getResource('Database');
		if ($db instanceof Database) {
			return new $name($db);
		}
		return null;
	}

}
