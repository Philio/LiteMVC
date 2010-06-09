<?php
/**
 * LiteMVC Application Framework
 * 
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App
 * @version 0.1.0
 */
namespace LiteMVC\App;

class Autoload
{

	/**
	 * List of autoload namespace/paths
	 *
	 * @var array
	 */
	private static $_paths = array();
	
	/**
	 * Autoload a class
	 * 
	 * @param string $class
	 * @return void
	 */
	public function loader($class)
	{
		// Check that a path has been set
		if (!count(self::$_paths)) {
			return;
		}
		// Check paths against class name
		foreach (self::$_paths as $ns => $path) {
			if (strpos($class, $ns) === 0) {
				$file = str_replace($ns, $path, $class);
				$file = str_replace('\\', '/', $file) . '.php';
				// Check exists
				if (file_exists($file)) {
					require_once $file;
				}
				break;
			}
		}
	}
	
	/**
	 * Register autoloader
	 * 
	 * @return void
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loader'));
	}
	
	/**
	 * Unregister autoloader
	 * 
	 * @return void
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loader'));
	}

	/**
	 * Set an autoload path
	 *
	 * @param string $namespace
	 * @param string $path
	 */
	public function setPath($namespace, $path)
	{
		self::$_paths[$namespace] = $path;
	}
	
}
