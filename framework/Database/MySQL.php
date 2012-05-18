<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\Database
 * @version 0.1.0
 */
namespace LiteMVC\Database;

class MySQL extends \mysqli
{

	/**
	 * Connection settings
	 * 
	 * @var string
	 */
	private $_host;
	private $_username;
	private $_password;
	private $_database;

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
		// Save settings
		$this->_host = $host;
		$this->_username = $username;
		$this->_password = $password;
		$this->_database = $database;
	}

	/**
	 * Sleep
	 * 
	 * @return array
	 */
	public function __sleep()
	{
		// Encrypt the password, not great but better than plain text
		$this->_password = base64_encode($this->_password);
		// Return array of params to save
		return array('_host', '_username', '_password', '_database', '_noerrors');
	}

	/**
	 * Wakeup
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		// Decrypt the password
		$this->_password = base64_decode($this->_password);
		// Reconnect
		parent::__construct($this->_host, $this->_username, $this->_password, $this->_database);
		// Check for errors
		if (!$this->_noerrors && $this->connect_errno) {
			throw new Exception('An error occcured reconnecting to the database.');
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
			throw new Exception('#' . $this->errno . ' ' . $this->error . ' occurred in query ' . $sql, $this->errno);
		}
		return $res;
	}

}