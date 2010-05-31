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
	 * String that separates nesting levels of configuration data identifiers
	 *
	 * @var string
	 */
	protected $_nestSeparator = '.';

	/**
	 * String that separates the parent section name
	 *
	 * @var string
	 */
	protected $_sectionSeparator = ':';

	/**
	 * Constructor
	 *
	 * @param string $filename
	 * @param string $section
	 * @return void
	 */
	public function __construct($filename, $section = null)
	{
		if (empty($filename)) {
			throw new Config\Exception('Please specifiy the name of the configuration file.');
		}
		// Read ini file
		$iniArray = $this->_loadIniFile($filename);
		// Process ini file
		if ($section === null) {
			// Load entire file
			$dataArray = array();
			foreach ($iniArray as $sectionName => $sectionData) {
				if(!is_array($sectionData)) {
					$dataArray = array_merge_recursive($dataArray, $this->_processKey(array(), $sectionName, $sectionData));
				} else {
					$dataArray[$sectionName] = $this->_processSection($iniArray, $sectionName);
				}
			}
			parent::__construct($dataArray);
		} else {
			// Load one or more sections
			if (!is_array($section)) {
				$section = array($section);
			}
			$dataArray = array();
			foreach ($section as $sectionName) {
				if (!isset($iniArray[$sectionName])) {
					throw new Config\Exception('Section \'' . $sectionName . '\' not found in ' . $filename . '.');
				}
				$dataArray = array_merge($this->_processSection($iniArray, $sectionName), $dataArray);
			}
			parent::__construct($dataArray);
		}
	}

	/**
	 * Load the ini file
	 *
	 * @param string $filename
	 * @return array
	 */
	protected function _loadIniFile($filename)
	{
		if (!file_exists($filename)) {
			throw new Config\Exception('File \'' . $filename . '\' doesn\'t exist.');
		}
		$data = parse_ini_file($filename, true);
		$iniArray = array();
		foreach ($data as $key => $data) {
			$pieces = explode($this->_sectionSeparator, $key);
			$thisSection = trim($pieces[0]);
			switch (count($pieces)) {
				case 1:
					$iniArray[$thisSection] = $data;
					break;
				case 2:
					$extendedSection = trim($pieces[1]);
					$iniArray[$thisSection] = array_merge(array(';extends'=>$extendedSection), $data);
					break;
				default:
					throw new Config\Exception('Section \'' . $thisSection . '\' can not extend multiple sections.');
			}
		}
		return $iniArray;
	}

	/**
	 * Process secotion
	 *
	 * @param array $iniArray
	 * @param string $section
	 * @param array $config
	 * @return array
	 */
	protected function _processSection($iniArray, $section, $config = array())
	{
		$thisSection = $iniArray[$section];
		foreach ($thisSection as $key => $value) {
			if (strtolower($key) == ';extends') {
				if (isset($iniArray[$value])) {
					$config = $this->_processSection($iniArray, $value, $config);
				} else {
					throw new Config\Exception('Parent section \'' . $section . '\' not found.');
				}
			} else {
				$config = $this->_processKey($config, $key, $value);
			}
		}
		return $config;
	}

	/**
	 * Process a key
	 *
	 * @param array $config
	 * @param string $key
	 * @param string $value
	 * @return array
	 */
	protected function _processKey($config, $key, $value)
	{
		if (strpos($key, $this->_nestSeparator) !== false) {
			$pieces = explode($this->_nestSeparator, $key, 2);
			if (strlen($pieces[0]) && strlen($pieces[1])) {
				if (!isset($config[$pieces[0]])) {
					if ($pieces[0] === '0' && !empty($config)) {
						// convert the current values in $config into an array
						$config = array($pieces[0] => $config);
					} else {
						$config[$pieces[0]] = array();
					}
				} elseif (!is_array($config[$pieces[0]])) {
					throw new Config\Exception('Cannot create sub-key for \'' . $pieces[0] . '\' as key exists.');
				}
				$config[$pieces[0]] = $this->_processKey($config[$pieces[0]], $pieces[1], $value);
			} else {
				throw new Config\Exception('Invalid key \'' . $key . '\'.');
			}
		} else {
			$config[$key] = $value;
		}
		return $config;
	}

}