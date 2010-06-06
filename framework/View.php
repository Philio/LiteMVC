<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package Main
 */
namespace LiteMVC;

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
	 * Helper objects
	 *
	 * @var array
	 */
	protected $_helpers = array();

	/**
	 * Constructor
	 *
	 * @param App $app
	 * @return void
	 */
	public function __construct(App $app)
	{
		$this->_path = \PATH . $app::Path_App;
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
		if (substr($key, 0, 1) != '_') {
			$this->$key = $value;
		}
	}

	/**
	 * Get a value
	 *
	 * @return mixed;
	 */
	public function __get($key)
	{
		if (substr($key, 0, 1) != '_' && isset($this->$key)) {
			return $this->$key;
		}
		return null;
	}

	/**
	 * Call magic method
	 *
	 * @param string $name
	 * @param array $args
	 * @return void
	 * @todo add this!
	 */
	public function  __call($name, $args) {

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
	 * Set the page layout
	 *
	 * @param string $layout
	 * @return void
	 */
	public function setLayout($layout)
	{
		$this->_layout = $layout;
	}

	/**
	 * Set the page
	 * 
	 * @param string $page
	 * @return void
	 */
	public function setPage($page)
	{
		$this->_page = $page;
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