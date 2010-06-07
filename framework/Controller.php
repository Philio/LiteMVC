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
		// Assign request/view to class vars
		$this->_request = $app->getResource('Request');
		if ($app->isResource('View\HTML')) {
			$this->_view = $app->getResource('View\HTML');
			// Set module
			$module = $this->_request->getModule();
			$this->_view->setModule($module);
			// Set layout
			$config = $app->getResource('Config')->Request;
			if (isset($config[$module][$controller]['layout'])) {
				$this->_view->setLayout(ucfirst($config[$module][$controller]['layout']));
			} elseif (isset($config[$this->_request->getModule()]['default']['layout'])) {
				$this->_view->setLayout(ucfirst($config[$module]['default']['layout']));
			}
			// Set page
			$this->_view->setPage(ucfirst($controller) . '/' . ucfirst($action));
		} elseif ($app->isResource('View\JSON')) {
			$this->_view = $app->getResource('View\JSON');
		}
	}

}
