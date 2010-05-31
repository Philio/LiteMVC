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
				unset($usd['expires']);
				return $usd;
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
		$var['expires'] = $expire > time() ? $expire : $expire + time();
		// Write to file
		$res = fwrite($f, serialize($var));
		if ($res === false) {
			throw new File\Exception('Unable to write to the output file.');
		}
		fclose($f);
		return true;
	}
	
}