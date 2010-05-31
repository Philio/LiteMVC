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
		
	}
	
}