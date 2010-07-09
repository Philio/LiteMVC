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

class Database implements Session
{
	
	/**
	 * Database object
	 *
	 * @var Db
	 */
	protected $_db;

	/**
	 * Config object
	 * 
	 * @var Config
	 */
	protected $_config;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 */
	public function __construct($db, $config)
	{
		// Assign database connetion and config settings
		$this->_db = $db;
		// Check config
		if (isset($config['table'], $config['fields']['id'], $config['fields']['data'], $config['fields']['expires'])) {
			$this->_config = $config;
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
		// Look for session
		$result = $this->_db->query(
			'SELECT ' . $this->_config['fields']['data'] . ' FROM ' . $this->_config['table'] .
			' WHERE ' . $this->_config['fields']['id'] . " = '$id' AND " . $this->_config['fields']['expires'] .
			' > UNIX_TIMESTAMP()'
		);
		// If session found return session data
		if ($result !== false && $result->num_rows) {
			$row = $result->fetch_object();
			$data = $row->{$this->_config['fields']['data']};
			return $data;
		}
		return false;
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
		// Overwrite existing session data
		$this->_db->query(
			'REPLACE INTO ' . $this->_config['table'] . " VALUES ('$id', '$data', $expiry)"
		);
	}
	
	/**
	 * Destroy session
	 * 
	 * @param string $id
	 * @return void
	 */
	public function destroy($id)
	{
		// Delete the session
		$this->_db->query(
			'DELETE FROM ' . $this->_config['table'] . ' WHERE ' . $this->_config['fields']['id'] . " = '$id'"
		);
	}
	
	/**
	 * Garbage collection
	 * 
	 * @return void
	 */
	public function gc()
	{
		// Delete old sessions, limited to 100 records to avoid slow load
		$this->_db->query(
			'DELETE FROM ' . $this->_config['table'] . ' WHERE ' . $this->_config['fields']['expires'] . ' < UNIX_TIMESTAMP() LIMIT 100'
		);
	}
	
}