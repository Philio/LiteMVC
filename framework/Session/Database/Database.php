<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.0
 */
namespace LiteMVC\Session\Database;

interface Database
{

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
	 * @param int $expiry
	 * @return void
	 */
	public function write($id, $data, $expiry);

	/**
	 * Destory session
	 *
	 * @param string $id
	 * @return void
	 */
	public function destroy($id);

	/**
	 * Garbage collection
	 *
	 * @return void
	 */
	public function gc();

}