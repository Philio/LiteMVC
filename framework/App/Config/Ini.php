<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App\Config
 */
namespace LiteMVC\App\Config;

// Namespace aliases
use LiteMVC\App\Config as Config;

class Ini extends Config
{

	/**
	 * Section separator
	 * 
	 * @var string
	 */
	const Section_Separator = ':';
	
	/**
	 * Item separator
	 * 
	 * @var string
	 */
	const Item_Separator = '.';

	/**
	 * Constructor
	 *
	 * @param string $filename
	 * @param string $section
	 * @return void
	 */
	public function __construct($file, $section)
	{
		$ini = $this->_read($file);
		$this->_data = $this->_processIni($ini, $section);
	}

	/**
	 * Read the ini file
	 *
	 * @param string $filename
	 * @return array
	 */
	protected function _read($filename)
	{
		if (file_exists($filename)) {
			$ini = parse_ini_file($filename, true);
			return $ini;
		} else {
			throw new Config\Exception('File \'' . $filename . '\' not found.');
		}
	}

	/**
	 * Process the ini data
	 * 
	 * @param array $ini
	 * @param string $section
	 */
	protected function _processIni($ini, $section)
	{
		// Check if section exists
		if (isset($ini[$section])) {
			return $this->_processSection($ini[$section]);
		}
		// Otherwise look and check for sections with extends
		foreach ($ini as $key => $value) {
			// If separator found split and check
			if (strpos($key, self::Section_Separator) !== false) {
				$parts = explode(self::Section_Separator, $key, 2);
				// Check for section with extend
				if (trim($parts[0]) == $section) {
					if (strpos($parts[1], self::Section_Separator) !== false) {
						throw new Config\Exception('A section can not extend multiple sections.');
					}
					return array_merge_recursive($this->_processIni($ini, trim($parts[1])), $this->_processSection($value));
				}
			}
		}
	}
	
	/**
	 * Process a section of the ini file
	 * 
	 * @param array $data
	 */
	protected function _processSection($data) {
		$config = array();
		foreach ($data as $key => $value) {
			$config = $this->_processKey($config, $key, $value);
		}
		return $config;
	}
	
	/**
	 * Process a key
	 * 
	 * @param array $config
	 * @param string $key
	 * @param mixed $value
	 */
	protected function _processKey($config, $key, $value)
	{
		if (strpos($key, self::Item_Separator) !== false) {
			$parts = explode(self::Item_Separator, $key, 2);
			if (!isset($config[$parts[0]])) {
				$config[$parts[0]] = array();
			}
			$config[$parts[0]] = $this->_processKey($config[$parts[0]], $parts[1], $value);
		} else {
			$config[$key] = $value;
		}
		return $config;
	}

}