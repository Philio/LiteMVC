<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 */
namespace LiteMVC\Session;

class Memcache implements Session
{

	/**
	 * Memcache object
	 *
	 * @var LiteMVC\Cache\Memcache
	 */
	protected $_memcache;


	/**
	 * Key prefix
	 *
	 * @var string
	 */
	protected $_prefix;

	/**
	 * Constructor
	 *
	 * @param LiteMVC\Cache\Memcache $memcache
	 * @param LiteMVC\App\Config $config
	 * @return void
	 */
	public function __construct($memcache, $config)
	{
		$this->_memcache = $memcache;
		if (isset($config['prefix'])) {
			$this->_prefix = $config['prefix'];
		}
	}

	/**
	 * Open session
	 *
	 * @param string $path
	 * @param string $name
	 * @return void
	 */
	public function open($path, $name) {}

	/**
	 * Close session
	 *
	 * @return void
	 */
	public function close() {}

	/**
	 * Read session data
	 *
	 * @param string $id
	 * @return string
	 */
	public function read($id)
	{
		return $this->_memcache->get($this->_prefix . $id);
	}

	/**
	 * Write session data
	 *
	 * @param string $id
	 * @param string $data
	 * @return void
	 */
	public function write($id, $data, $expiry)
	{
		$this->_memcache->set($this->_prefix . $id, $data, 0, $expiry);
	}

	/**
	 * Destroy session
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{
		$this->_memcache->delete($this->_prefix . $id);
	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc() {}

}