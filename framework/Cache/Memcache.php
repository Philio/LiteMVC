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
use LiteMVC\Cache\Memcache as Memcache;

class Memcache extends \Memcache
{

	/**
	 * Constructor
	 * 
	 * @param mixed $servers
	 * @return void
	 */
	public function __construct($servers)
	{
		// If App object provided, extract config
		if ($servers instanceof App) {
			// Get memcache config from App object
			$config = $servers->getResource('Config')->memcache;
			if (!is_null($config) && isset($config['servers'])) {
				$servers = $config['servers'];
			}
		}
		// Add specified servers to pool
		if (is_array($servers)) {
			foreach ($servers as $server) {
				$this->_addServerFromString($server);
			}
		} elseif (is_string($servers)) {
			$this->_addServerFromString($servers);
		} else {
			throw new Memcache\Exception('Unknown server type specified, must be an array or a string.');
		}
	}
	
	/**
	 * Add server from supplied string
	 * 
	 * @param string $server
	 * @return bool
	 */
	private function _addServerFromString($server)
	{
		list ($host, $port) = explode(':', $server);
		$res = parent::addServer($host, $port);
		if ($res === false) {
			throw new Memcache\Exception('There was an error adding server ' . $server . '.');
		}
		return $res;
	}
	
}