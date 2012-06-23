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

// Namespace aliases
use LiteMVC\Config as Config;

class Config extends Resource\Dataset implements \Countable
{

	/**
	 * Cache module
	 *
	 * @var Cache
	 */
	protected $_cache;

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