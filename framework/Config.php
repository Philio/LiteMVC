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
namespace LiteMVC;

// Namespace aliases
use LiteMVC\Config as Config;

class Config implements \Countable
{

	/**
	 * Cache module
	 *
	 * @var Cache
	 */
	protected $_cache;

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Cache prefix
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'Config';

	/**
	 * Cache lifetime
	 *
	 * @var int
	 */
	const CACHE_LIFETIME = 86400;

	/**
	 * Set magic method
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value)
	{
		$this->_data[$name] = $value;
	}

	/**
	 * Get magic method
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		}
		return null;
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
	public function &getVal($name)
	{
		if (array_key_exists($name, $this->_data)) {
			return $this->_data[$name];
		}
		return null;
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

	/**
	 * Set cache module
	 *
	 * @param Cache $cache
	 */
	public function setCache($cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * Read config from cache
	 *
	 * @param string $file
	 * @return bool
	 */
	protected function _readCache($file)
	{
		// If no cache module return false
		if (is_null($this->_cache)) {
			return false;
		}
		// Read from cache
		$data = $this->_cache->get(self::CACHE_PREFIX . '_' . md5($file));
		// Check if cache is valid
		if ($data === false || $data['fmt'] < filemtime($file)) {
			return false;
		}
		$this->_data = $data['contents'];
		return true;
	}

	/**
	 * Write data to cache
	 *
	 * @param string $file
	 * @return void
	 */
	protected function _writeCache($file)
	{
		// If no cache module return false
		if (is_null($this->_cache)) {
			return false;
		}
		// Write data to cache
		$this->_cache->set(
			self::CACHE_PREFIX . '_' . md5($file),
			array(
				'fmt' => filemtime($file),
				'contents' => $this->_data
			),
			2,
			self::CACHE_LIFETIME
		);
	}

}