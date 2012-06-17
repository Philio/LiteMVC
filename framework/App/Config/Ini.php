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
	const SECTION_SEPARATOR = ':';

	/**
	 * Item separator
	 *
	 * @var string
	 */
	const ITEM_SEPARATOR = '.';

	/**
	 * Load configuration from file
	 *
	 * @param string $filename
	 * @param string $section
	 * @return void
	 */
	public function load($file, $section)
	{
		if (!$this->_readCache($file)) {
			$ini = $this->_read($file);
			$this->_data = $this->_processIni($ini, $section);
			// Save config in cache
			$this->_writeCache($file);
		}
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
			if (strpos($key, self::SECTION_SEPARATOR) !== false) {
				$parts = explode(self::SECTION_SEPARATOR, $key, 2);
				// Check for section with extend
				if (trim($parts[0]) == $section) {
					if (strpos($parts[1], self::SECTION_SEPARATOR) !== false) {
						throw new Config\Exception('A section can not extend multiple sections.');
					}
					return $this->_arrayMerge(
						$this->_processIni($ini, trim($parts[1])),
						$this->_processSection($value)
					);
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
		if (strpos($key, self::ITEM_SEPARATOR) !== false) {
			$parts = explode(self::ITEM_SEPARATOR, $key, 2);
			if (!isset($config[$parts[0]])) {
				$config[$parts[0]] = array();
			}
			$config[$parts[0]] = $this->_processKey(
				$config[$parts[0]], $parts[1], $value
			);
		} else {
			$config[$key] = $value;
		}
		return $config;
	}

	/**
	 * A multi dimensional array merge replacing existing keys
	 *
	 * @param array $arrStart
	 * @param array $arrAdd
	 */
	protected function _arrayMerge(array $arrStart, array $arrAdd)
	{
		// Loop through array
		foreach ($arrAdd as $key => $value) {
			// Call recursively for arrays
			if (array_key_exists($key, $arrStart) && is_array($value)) {
				$arrStart[$key] = $this->_arrayMerge($arrStart[$key], $value);
			} else {
				$arrStart[$key] = $value;
			}
		}
		// Return merged array
		return $arrStart;
	}

}