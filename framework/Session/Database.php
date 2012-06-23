<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
 */
namespace LiteMVC\Session;

class Database implements Session
{

	/**
	 * Model object
	 *
	 * @var Model
	 */
	protected $_model;

	/**
	 * Configuration keys
	 *
	 * @var string
	 */
	const CONFIG_MODEL = 'model';

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct($db, $config)
	{
		// Check config
		if (isset($config[self::CONFIG_MODEL])) {
			// Instantiate model
			$this->_model = new $config[self::CONFIG_MODEL]($db);
		} else {
			throw new Exception('The session database configuration is invalid.');
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
		// Call model read
		return $this->_model->read($id);
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
		// Call model write
		$this->_model->write($id, $data, $expiry);
	}

	/**
	 * Destroy session
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{
		// Call model destroy
		$this->_model->destroy($id);
	}

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc()
	{
		// Call model garbage collection
		$this->_model->gc();
	}

}