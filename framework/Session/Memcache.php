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
	 * Constructor
	 *
	 * @param LiteMVC\Cache\Memcache $memcache
	 * @return void
	 */
	public function __construct($memcache)
	{
		$this->_memcache = $memcache;
	}

	/**
	 * Open session
	 *
	 * @param string $path
	 * @param string $name
	 */
	public function open($path, $name)
	{

	}

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

	}

	/**
	 * Write session data
	 *
	 * @param string $id
	 * @param string $data
	 */
	public function write($id, $data, $expiry)
	{

	}

	/**
	 * Destroy session
	 *
	 * @param string $id
	 */
	public function destroy($id)
	{

	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc()
	{

	}

}