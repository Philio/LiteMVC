<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App
 */
namespace LiteMVC\App;

// Namespace aliases
use LiteMVC\App\Config as Config;

class Config implements \Countable
{

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Get magic method
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		}
		return null;
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
		if (is_array($value)) {
			$this->_data[$name] = new self($value, true);
		} else {
			$this->_data[$name] = $value;
		}
	}

	/**
	 * Isset magic method
	 *
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->_data[$name]);
	}

	/**
	 * Get count of items
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_data);
	}

}