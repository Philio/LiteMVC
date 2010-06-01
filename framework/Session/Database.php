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
		$this->_db = $db;
		$this->_config = $config;
	}
	
	/**
	 * Open session
	 * 
	 * @param string $path
	 * @param string $name
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
		$result = $this->_db->query(
			'SELECT ' . $this->_config->fields->data . ' FROM ' . $this->_config->tablename . 
			' WHERE ' . $this->_config->fields->id . " = '$id' AND " . $this->_config->fields->expires .
			' > UNIX_TIMESTAMP()'
		);
		if ($result instanceof mysqli_result && $result->num_rows) {
			$row = $result->fetch_object();
			$data = $row->{$this->_config->fields->data};
			return $data;
		}
		return false;
	}
	
	/**
	 * Write session data
	 * 
	 * @param string $id
	 * @param string $data
	 */
	public function write($id, $data, $expiry)
	{
		$this->_db->query(
			'REPLACE INTO ' . $this->_config->tablename ." VALUES ('$id', '$data', $expiry)"
		);
	}
	
	/**
	 * Destroy session
	 * 
	 * @param string $id
	 */
	public function destroy($id)
	{
		$this->_db->query(
			'DELETE FROM ' . $this->_config->tablename . ' WHERE ' . $this->_config->fields->id . " = '$id'"
		);
	}
	
	/**
	 * Garbage collection
	 * 
	 * @return void
	 */
	public function gc()
	{
		$this->_db->query(
			'DELETE FROM ' . $this->_config->tablename . ' WHERE ' . $this->_config->fields->expiry . ' < UNIX_TIMESTAMP()'
		);
	}
	
}