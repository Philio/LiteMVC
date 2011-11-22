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
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONF_LOGIN	= 'login';
	const CONF_ACTION	= 'action';
	const CONF_CTRLR	= 'controller';
	const CONF_ERR		= 'error';
	const CONF_NA		= 'notallowed';
	const CONF_NF		= 'notfound';
	const CONF_EXP		= 'exception';

	/**
	 * Resource names
	 *
	 * @var string
	 */
	const RES_REQUEST	= 'Request';
	const RES_AUTH		= 'Authenticate';
	const RES_HTML		= 'View\HTML';
	const RES_JSON		= 'View\JSON';

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
		$this->_request = $app->getResource(self::RES_REQUEST);
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
		$exception = null;

		while (!$dispatched) {
			try {
				// Authenticate module checks
				if ($this->_app->isResource(self::RES_AUTH)) {
					$auth = $this->_app->getResource(self::RES_AUTH);

					// Check if user is allowed to view this page
					if (!$auth->isAllowed($module, $controller, $action)) {
						// Redirect to login
						if ($auth->hasUserModel() && !$auth->isLoggedIn()) {
							$login = $this->_request->getConfig(self::CONF_LOGIN);
							if (is_null($login)) {
								throw new App\Exception('Unable to redirect to login page, configuration is invalid.');
							}
							$this->_request->redirect($login[self::CONF_ACTION], $login[self::CONF_CTRLR]);

						} else {
							// Display access denied page
							$error = $this->_request->getConfig(self::CONF_ERR);
							if (!is_null($error) && isset($error[self::CONF_CTRLR]) && isset($error[self::CONF_NA])) {
								$controller = $error[self::CONF_CTRLR];
								$action = $error[self::CONF_NA];
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
					$c = new $class($this->_app, $controller, $action, $exception);
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
				$error = $this->_request->getConfig(self::CONF_ERR);
				if (isset($error[self::CONF_CTRLR]) && isset($error[self::CONF_NF])) {
					$controller = $error[self::CONF_CTRLR];
					$action = $error[self::CONF_NF];
				}

			} catch (\Exception $e) {
				// Catch any other exceptions within the application
				if ($exception) {
					throw new App\Exception('Uncaught exception. Unable to load error page, configuration is invalid.');
				}
				$exception = $e;
				$error = $this->_request->getConfig(self::CONF_ERR);
				if (isset($error[self::CONF_CTRLR]) && isset($error[self::CONF_EXP])) {
					$controller = $error[self::CONF_CTRLR];
					$action = $error[self::CONF_EXP];
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
