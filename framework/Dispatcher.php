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
use LiteMVC\App as App;

class Dispatcher {

	/**
	 * App object
	 *
	 * @var App
	 */
	protected $_app;

	/**
	 * Config objet
	 *
	 * @var App\Config
	 */
	protected $_config;

	/**
	 * Request object
	 *
	 * @var Request
	 */
	protected $_request;

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Assign app, config and request to class vars
		$this->_app = $app;
		$this->_config = $app->getResource('Config')->Request;
		$this->_request = $app->getResource('Request');
	}

	/**
	 * Dispatch request
	 *
	 * @return void
	 */
	public function dispatch()
	{
		// Get module, controller and action from request object
		$module = $this->_request->getModule();
		$controller = $this->_request->getController();
		$action = $this->_request->getAction();
		// Try and dispatch the request
		$dispatched = false;
		$notfound = false;
		$error = false;
		while (!$dispatched) {
			try {
				// Get class and action names
				$class = $this->fmtClass($module, $controller);
				$method = $this->fmtAction($action);
				// Try and load the class
				if (class_exists($class)) {
					$c = new $class($this->_app, $controller, $action);
				} else {
					throw new App\Exception('Unable to load controller class for ' . $controller . '.');
				}
				// Try and load the action
				if (method_exists($c, $method)) {
					$c->$method();
				} else {
					throw new App\Exception('Unable to load action method for  ' . $action . '.');
				}
				// Dispatched ok
				$dispatched = true;
			} catch (App\Exception $e) {
				// Page not found, display error page
				if ($notfound) {
					throw new App\Exception('Unable to load error page, configuration is invalid.');
				}
				$notfound = true;
				if (isset($this->_config[$module]['error']['controller']) && isset($this->_config[$module]['error']['notfound'])) {
					$controller = $this->_config[$module]['error']['controller'];
					$action = $this->_config[$module]['error']['notfound'];
				}
			} catch (\Exception $e) {
				// Catch any other exceptions within the application
				if ($error) {
					throw new App\Exception('Unable to load error page, configuration is invalid.');
				}
				$error = true;
				if (isset($this->_config[$module]['error']['controller']) && isset($this->_config[$module]['error']['exception'])) {
					$controller = $this->_config[$module]['error']['controller'];
					$action = $this->_config[$module]['error']['exception'];
				}
			}
		}
	}

	/**
	 * Format controller as valid class/namespace
	 *
	 * @param string $module
	 * @param string $controller
	 * @return string
	 */
	public function fmtClass($module, $controller)
	{
		return ucfirst($module) . '\\Controller\\' . ucfirst($controller);
	}

	/**
	 * Format action
	 *
	 * @param string $action
	 * @return string
	 */
	public function fmtAction($action)
	{
		// Covert dashes to capitals
		$parts = explode('-', $action);
		$action = '';
		foreach ($parts as $part) {
			$action .= ucfirst($part);
		}
		$action .= 'Action';
		return $action;
	}

}
?>
