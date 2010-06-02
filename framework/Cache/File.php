<?php
/**
 * LiteMVC Application Framework
 * 
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Cache
 */
namespace LiteMVC\Cache;

// Namespace aliases
use LiteMVC\App as App;
use LiteMVC\Cache\File as File;

class File
{
	
	/**
	 * Cache path
	 * 
	 * @var string
	 */
	private $_path;
	
	/**
	 * Constructor
	 * 
	 * @param string $path
	 * @return void
	 */
	public function __construct($path)
	{
		// If App object provided, extract config
		if ($path instanceof App) {
			// Get file cache config from App object
			$config = $path->getResource('Config');
			if ($config && !is_null($config->Filecache) && isset($config->Filecache['path'])) {
				$path = $config->Filecache['path'];
			} else {
				$path = \PATH . $path::Path_Cache;
			}
		}
		$this->_path = $path;
	}
	
	/**
	 * Get a value from file cache
	 * 
	 * @param string $key
	 */
	public function get($key)
	{
		// Check if file exists
		if (file_exists($this->_path . $key)) {
			// Read file data
			$f = fopen($this->_path . $key, 'r');
			if ($f === false) {
				throw new File\Exception('Unable to open the input file.');
			}
			$data = fread($f, filesize($this->_path . $key));
			fclose($f);
			// Unserialise the data
			$usd = unserialize($data);
			if ($usd['expires'] > time()) {
				if ($usd['encoded']) {
					return base64_decode($usd['data']);
				}
				return $usd['data'];
			}
		}
		return false;
	}
	
	/**
	 * Set a value to file cache
	 * 
	 * @param string $key
	 * @param mixed $var
	 * @param int $flag (unused, this is to enable drop in replacement with memcache)
	 * @param int $expire
	 */
	public function set($key, $var, $flag, $expire)
	{
		// Create file
		$f = fopen($this->_path . $key, 'w');
		if ($f === false) {
			throw new File\Exception('Unable to open the output file.');
		}
		// Store expiry within file
		$data = array(
			'expires' => $expire > time() ? $expire : $expire + time(),
			'data' => is_string($var) ? base64_encode($var) : $var,
			'encoded' => is_string($var) ? true : false
		);
		// Write to file
		$res = fwrite($f, serialize($data));
		if ($res === false) {
			throw new File\Exception('Unable to write to the output file.');
		}
		fclose($f);
		return true;
	}
	
}