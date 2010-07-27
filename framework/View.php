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

use LiteMVC\View as View;

abstract class View
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
	 * Page data
	 *
	 * @var array
	 */
	protected $_data = array();

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
	 * Helper objects
	 *
	 * @var array
	 */
	protected $_helpers = array();

	/**
	 * Default namespace prefix
	 *
	 * @var stirng
	 */
	const Namespace_Prefix = 'LiteMVC';

	/**
	 * Namespace body for helpers
	 *
	 * @var string
	 */
	const Namespace_Body = '\View\Helper\\';

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		// Set path
		$this->_path = \PATH . $app::Path_App;
		// Set theme module
		if ($app->isResource('Theme')) {
			$this->_theme = $app->getResource('Theme');
		}
	}

	/**
	 * Set a value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
	}

	/**
	 * Get a value
	 *
	 * @return mixed;
	 */
	public function __get($key)
	{
		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}
		return null;
	}

	/**
	 * Call magic method
	 *
	 * @param string $name
	 * @param array $args
	 * @return void
	 */
	public function __call($name, $args) {
		// Check if helper is instanciated
		if (isset($this->_helpers[$name])) {
			return $this->_helpers[$name];
		}
		// Check if class exists within framework namespace or app namespace
		$class = self::Namespace_Prefix . self::Namespace_Body . $name;
		if (!class_exists($class)) {
			$class = $this->_module . self::Namespace_Body . $name;
			if (!class_exists($class)) {
				return false;
			}
		}
		// Call class
		$this->_helpers[$name] = new $class();
		return $this->_helpers[$name];
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

}