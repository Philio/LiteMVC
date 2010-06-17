<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App
 * @version 0.1.0
 */
namespace LiteMVC\App;

// Namespace aliases
use LiteMVC\App\Config as Config;

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
	const Cache_Prefix = 'Config';

	/**
	 * Cache lifetime
	 *
	 * @var int
	 */
	const Cache_Lifetime = 86400;

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
		$data = $this->_cache->get(self::Cache_Prefix . '_' . md5($file));
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
			self::Cache_Prefix . '_' . md5($file),
			array(
				'fmt' => filemtime($file),
				'contents' => $this->_data
			),
			2,
			self::Cache_Lifetime
		);
	}

}