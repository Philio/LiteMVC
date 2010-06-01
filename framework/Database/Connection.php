<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Database
 */
namespace LiteMVC\Database;

class Connection extends \mysqli
{

	/**
	 * Suppress error messages (mainly for sessions)
	 * 
	 * @var bool
	 */
	private $_noerrors;

	/**
	 * Constructor
	 *
	 * @param string $host
	 * @param string $username
	 * @param string $password
	 * @param string $database
	 * @return void
	 */
	public function __construct($host, $username, $password, $database, $noerrors)
	{
		// Set error setting
		$this->_noerrors = $noerrors;
		// Connect
		parent::__construct($host, $username, $password, $database);
		// Check for errors
		if (!$this->_noerrors && $this->connect_errno) {
			throw new Exception('An error occcured connecting to the database.');
		}
	}

	/**
	 * Query the database
	 *
	 * @param string $sql
	 * @return bool|mysqli_result
	 */
	public function query($sql)
	{
		// Run query
		$res = parent::query($sql);
		// Check result is valid
		if (!$this->_noerrors && ($res === false || $this->errno != 0)) {
			throw new Exception('An error occured in query: ' . $sql);
		}
		return $res;
	}

}