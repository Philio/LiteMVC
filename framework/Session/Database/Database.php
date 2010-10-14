<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Session
 * @version 0.1.0
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