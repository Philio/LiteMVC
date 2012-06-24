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
namespace LiteMVC\Resource;

// Namespace aliases
use LiteMVC\Resource as Resource;

abstract class Dataset extends Resource implements \ArrayAccess, \Iterator, \Countable
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

	/**
	 * Set an offset
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

	/**
	 * Get an offset
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

	/**
	 * Check if an offset exists
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists ($offset)
	{
		return isset($this->_data[$offset]);
	}

	/**
	 * Unset an offset
	 *
	 * @param mixed $offset
	 */
	public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

	/**
	 * Get current element
	 *
	 * @return mixed
	 */
	public function current()
	{
		return current($this->_data);
	}

	/**
	 * Get key of the current element
	 *
	 * @return mixed
	 */
	public function key()
	{
		return key($this->_data);
	}

	/**
	 * Move to next element
	 *
	 * @return void
	 */
	public function next()
	{
		next($this->_data);
	}

	/**
	 * Reset internal pointer
	 *
	 * @return void
	 */
	public function rewind()
	{
		reset($this->_data);
	}

	/**
	 * Is current element valid
	 *
	 * @return bool
	 */
	public function valid()
	{
		return current($this->_data) ? true : false;
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