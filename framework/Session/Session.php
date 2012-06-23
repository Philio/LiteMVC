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

interface Session {

	/**
	 * Open session
	 *
	 * @param string $path
	 * @param string $name
	 */
	public function open($path, $name);

	/**
	 * Close session
	 *
	 * @return void
	 */
	public function close();

	/**
	 * Read session data
	 *
	 * @param string $id
	 * @return string
	 */
	public function read($id);

	/**
	 * Write session data
	 *
	 * @param string $id
	 * @param string $data
	 * @param string $expiry
	 */
	public function write($id, $data, $expiry);

	/**
	 * Destroy session
	 *
	 * @param string $id
	 */
	public function destroy($id);

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc();

}