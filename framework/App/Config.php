<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App
 */
namespace LiteMVC\App;

// Namespace aliases
use LiteMVC\App\Config as Config;

class Config implements \Countable
{

	/**
	 * Data
	 *
	 * @var array
	 */
	protected $_data;

	/**
	 * Construtor
	 *
	 * @param array $array
	 * @return void
	 */
	public function __construct($array)
	{
		$this->_data = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$this->_data[$key] = new self($value);
			} else {
				$this->_data[$key] = $value;
			}
		}
	}

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
	 * Convert data to array
	 *
	 * @return array
	 */
	public function toArray()
	{
		$array = array();
		$data = $this->_data;
		foreach ($data as $key => $value) {
			if ($value instanceof Config) {
				$array[$key] = $value->toArray();
			} else {
				$array[$key] = $value;
			}
		}
		return $array;
	}

	/**
	 * Merge another config
	 *
	 * @param Config $merge
	 * @return Config
	 */
	public function merge(App\Config $merge)
	{
		foreach($merge as $key => $item) {
			if(array_key_exists($key, $this->_data)) {
				if($item instanceof Config && $this->$key instanceof Config) {
					$this->$key = $this->$key->merge(new Config($item->toArray()));
				} else {
					$this->$key = $item;
				}
			} else {
				if($item instanceof Config) {
					$this->$key = new Config($item->toArray());
				} else {
					$this->$key = $item;
				}
			}
		}
		return $this;
	}

}