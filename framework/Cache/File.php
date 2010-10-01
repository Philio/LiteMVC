<?php
/**
 * LiteMVC Application Framework
 * 
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Cache
 * @version 0.1.0
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
	 * Encoding flags
	 * 
	 * @var int
	 */
	const Enc_None        = 0;
	const Enc_Serialize   = 1;
	const Enc_JSON_Array  = 2;
	const Enc_JSON_Object = 3;
	
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
			if ($config && !is_null($config->filecache) && isset($config->filecache['path'])) {
				$path = $config->filecache['path'];
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
			// Read header
			$header = fgets($f);
			if (strpos($header, '::') === false) {
				throw new File\Exception('Unable to read from the input file, bad header.');
			}
			list ($expire, $flag) = explode('::', $header);
			// Read data
                        if (filesize($this->_path . $key) - strlen($header) > 0) {
                            $body = fread($f, filesize($this->_path . $key) - strlen($header));
                        } else {
                            $body = null;
                        }
			switch ($flag) {
				default:
				case self::Enc_None:
					return $body;
					break;
				case self::Enc_Serialize:
					return unserialize($body);
					break;
				case self::Enc_JSON_Array:
					return json_decode($body, true);
					break;
				case self::Enc_JSON_Object:
					return json_decode($body);
					break;
			}
		}
		return false;
	}
	
	/**
	 * Set a value to file cache
	 * 
	 * @param string $key
	 * @param mixed $var
	 * @param int $flag (this emulates memcache but we will use it for the encoding method)
	 * @param int $expire
	 * @return bool
	 */
	public function set($key, $var, $flag, $expire)
	{
		// Create file
		$f = fopen($this->_path . $key, 'w');
		if ($f === false) {
			throw new File\Exception('Unable to open the output file.');
		}
		// Write header
		$header = ($expire > time() ? $expire : $expire + time()) . '::' . $flag . \PHP_EOL;
		$res = fwrite($f, $header);
		if ($res === false) {
			throw new File\Exception('Unable to write to the output file.');
		}
		// Write data
		switch ($flag) {
			default:
			case self::Enc_None:
				$data = $var;
				break;
			case self::Enc_Serialize:
				$data = serialize($var);
				break;
			case self::Enc_JSON_Array:
			case self::Enc_JSON_Object:
				$data = json_encode($var);
				break;
		}
		$res = fwrite($f, $data);
		if ($res === false) {
			throw new File\Exception('Unable to write to the output file.');
		}
		fclose($f);
		return true;
	}
	
}