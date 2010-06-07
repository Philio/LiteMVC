<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC
 * @version 0.1.0
 */
namespace LiteMVC\Session;

class File implements Session
{

	/**
	 * File caching object
	 *
	 * @var LiteMVC\Cache\File
	 */
	protected $_file;


	/**
	 * Key prefix
	 *
	 * @var string
	 */
	protected $_prefix;

	/**
	 * Constructor
	 *
	 * @param LiteMVC\Cache\File $file
	 * @param LiteMVC\App\Config $config
	 * @return void
	 */
	public function __construct($file, $config)
	{
		$this->_file = $file;
		if (isset($config['prefix'])) {
			$this->_prefix = $config['prefix'];
		}
	}

	/**
	 * Open session LiteMVC\Cache\File
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
		return $this->_file->get($this->_prefix . $id);
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
		$this->_file->set($this->_prefix . $id, $data, 0, $expiry);
	}

	/**
	 * Destroy session
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{
		$this->_file->delete($this->_prefix . $id);
	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc() {}

}