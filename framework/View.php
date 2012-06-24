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

use LiteMVC\View as View;

abstract class View extends Resource\Dataset implements \Countable
{

	/**
	 * Path of templates
	 *
	 * @var string
	 */
	protected $_path;

	/**
	 * Module
	 *
	 * @var string
	 */
	protected $_module;

	/**
	 * Theme object
	 *
	 * @var Theme
	 */
	protected $_theme;

	/**
	 * Layout
	 *
	 * @var string
	 */
	protected $_layout;

	/**
	 * Page
	 *
	 * @var string
	 */
	protected $_page;

	/**
	 * Page mode
	 *
	 * @var string
	 */
	protected $_pageMode = 'include';

	/**
	 * Rendered page
	 *
	 * @var string
	 */
	protected $_pageRendered;

	/**
	 * Full rendered output
	 *
	 * @var string
	 */
	protected $_rendered;

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
	 * Namespace body for helpers
	 *
	 * @var string
	 */
	const NAMESPACE_BODY = '\\View\\Plugin\\';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Set path
		$this->_path = \PATH . $app::PATH_APP;
		// Set theme module
		if ($app->isResource('Theme')) {
			$this->_theme = $app->getResource('Theme');
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
	 * Set the module
	 *
	 * @param string $module
	 * @return void
	 */
	public function setModule($module)
	{
		$this->_module = $module;
	}

	/**
	 * Set the page rendering mode
	 *
	 * @param string $mode
	 * @return void
	 */
	public function setPageMode($mode)
	{
		if (in_array($mode, array('include', 'replace'))) {
			$this->_pageMode = $mode;
		}
	}

	/**
	 * Set the page layout
	 *
	 * @param string $layout
	 * @return void
	 */
	public function setLayout($layout)
	{
		if (is_null($layout)) {
			return;
		}
		if (is_null($this->_module)) {
			throw new View\Exception('Cannot determine path, module unknown');
		}
		$layout = $this->_path . $this->_module . '/View/Layouts/' . $layout . '.phtml';
		// Run theme processing
		if ($this->_theme instanceof Theme) {
			$this->_layout = $this->_theme->layout($layout);
		} else {
			$this->_layout = $layout;
		}
	}

	/**
	 * Set the page
	 *
	 * @param string $page
	 * @return void
	 */
	public function setPage($page)
	{
		if (is_null($this->_module)) {
			throw new View\Exception('Cannot determine path, module unknown');
		}
		$page =  $this->_path . $this->_module . '/View/Pages/' . $page . '.phtml';
		// Run theme processing
		if ($this->_theme instanceof Theme) {
			$this->_page = $this->_theme->page($page);
		} else {
			$this->_page = $page;
		}
	}

	/**
	 * Get the rendered page
	 *
	 * @return string
	 */
	public function getPage()
	{
		return $this->_pageRendered . PHP_EOL;
	}

	/**
	 * toString magic method to get rendered output
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->_rendered;
	}

	/**
	 * Render the page
	 *
	 * @return void
	 */
	abstract public function render();

}