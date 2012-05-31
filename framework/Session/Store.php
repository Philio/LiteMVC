<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2012
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC\Session;

class Store implements \Countable
{
	
	/**
	 * Session namespace
	 * 
	 * @var string 
	 */
	private $_namespace = 'Store';
	
	/**
	 * Set namespace
	 * 
	 * @param string $namespace 
	 */
	public function __construct($namespace = null)
	{
		// Set namespace
		if (!is_null($namespace) && is_string($namespace)) {
			$this->_namespace = $namespace;
		}
		
		// Initialise
		$_SESSION[$this->_namespace] = array();
	}
	
	/**
	 * Set magic method
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->setVal($name, $value);
	}

	/**
	 * Get magic method
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->getVal($name);
	}
	
	/**
	 * Isset magic method
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($_SESSION[$this->_namespace][$name]);
	}

	/**
	 * Set value method
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setVal($name, $value)
	{
		$_SESSION[$this->_namespace][$name] = $value;
	}

	/**
	 * Get value method
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &getVal($name)
	{
		if (array_key_exists($name, $_SESSION[$this->_namespace])) {
			return $_SESSION[$this->_namespace][$name];
		}
		return null;
	}
	
	/**
	 * Set data to the store
	 * 
	 * @param array $data 
	 */
	public function setData(array $data)
	{
		$_SESSION[$this->_namespace] = $data;
	}
	
	/**
	 * Get all stored data
	 * 
	 * @return array 
	 */
	public function getData()
	{
		return $_SESSION[$this->_namespace];
	}

	/**
	 * Count items
	 * 
	 * @return int 
	 */
	public function count() {
		return count($_SESSION[$this->_namespace]);
	}
	
}