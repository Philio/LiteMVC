<?php
/**
 * LiteMVC Application Framework
 * 
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 */
namespace LiteMVC\App;

class Autoload
{

	/**
	 * List of autoload namespace/paths
	 *
	 * @var array
	 */
	private $_paths = array(
		'LiteMVC' => '/framework'
	);
	
	/**
	 * Autoload a class
	 * 
	 * @param string $class
	 * @return void
	 */
	public static function loader($class)
	{
		echo $class;
	}
	
	/**
	 * Register autoloader
	 * 
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register(__NAMESPACE__ . '\Autoload::loader');
	}
	
	/**
	 * Unregister autoloader
	 * 
	 * @return void
	 */
	public function unregister()
	{
		spl_autoload_unregister(__NAMESPACE__ . '\Autoload::loader');
	}
	
	public function addPath($namespace, $path)
	{
		$this->_paths[$namespace] = $path;
	}
	
}
