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
		$exception = false;
		while (!$dispatched) {
			try {
				// Authenticate module checks
				if ($this->_app->isResource('Authenticate')) {
					$auth = $this->_app->getResource('Authenticate');
					// Check if user is allowed to view this page
					if (!$auth->isAllowed($module, $controller, $action)) {
						// Redirect to login
						if ($auth->hasUserModel() && !$auth->isLoggedIn()) {
							$login = $this->_request->getConfig('login');
							if (is_null($login)) {
								throw new App\Exception('Unable to redirect to login page, configuration is invalid.');
							}
							$this->_request->redirect($login['action'], $login['controller']);
						} else {
							// Display access denied page
							$error = $this->_request->getConfig('error');
							if (!is_null($error) && isset($error['controller']) && isset($error['notallowed'])) {
								$controller = $error['controller'];
								$action = $error['notallowed'];
							} else {
								throw new App\Exception('Access denied. Unable to load error page, configuration is invalid.');
							}
						}
					}
				}
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
					// Call controller init function first if it exists
					if (method_exists($c, 'init')) {
						$c->init();
					}
					// Call action
					$c->$method();
				} else {
					throw new App\Exception('Unable to load action method for  ' . $action . '.');
				}
				// Dispatched ok
				$dispatched = true;
			} catch (App\Exception $e) {
				// Page not found, display error page
				if ($notfound) {
					throw new App\Exception('Page not found. Unable to load error page, configuration is invalid.');
				}
				$notfound = true;
				$error = $this->_request->getConfig('error');
				if (isset($error['controller']) && isset($error['notfound'])) {
					$controller = $error['controller'];
					$action = $error['notfound'];
				}
			} catch (\Exception $e) {
				// Catch any other exceptions within the application
				if ($exception) {
					throw new App\Exception('Uncaught exception. Unable to load error page, configuration is invalid.');
				}
				$exception = true;
				$error = $this->_request->getConfig('error');
				if (isset($error['controller']) && isset($error['exception'])) {
					$controller = $error['controller'];
					$action = $error['exception'];
					// Push error message into view for display
					if ($this->_app->isResource('View\HTML')) {
						$this->_app->getResource('View\HTML')->exception = $e;
					} elseif ($this->_app->isResource('View\JSON')) {
						$this->_app->getResource('View\JSON')->exception = $e;
					}
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
