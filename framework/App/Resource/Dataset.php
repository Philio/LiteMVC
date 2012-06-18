<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC\App\Resource;

// Namespace aliases
use LiteMVC\App\Resource as Resource;

abstract class Dataset extends Resource
{

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data = array();

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
		return $this->getVal($name, null);
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
		$this->_data[$name] = $value;
	}

	/**
	 * Get value method
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &getVal($name, $default = null)
	{
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		}
		return $default;
	}

}