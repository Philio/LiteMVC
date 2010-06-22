<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package LiteMVC\App
 * @version 0.1.0
 */
namespace LiteMVC;

// Namespace aliases
use LiteMVC\Model as Model;

abstract class Model
{

	/**
	 * Database connection
	 * 
	 * @var object 
	 */
	protected $_conn;

	/**
	 * Database name (namespace in config, should be overloaded by child)
	 *
	 * @var string
	 */
	protected $_database;

	/**
	 * Table name (should be overloaded by child)
	 *
	 * @var string
	 */
	protected $_table;

	/**
	 * The primary key for the table (should be overloaded by child)
	 *
	 * @var mixed
	 */
	protected $_primary = null;

	/**
	 * Data contained within a row
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Has a row been loaded from the database
	 *
	 * @var bool
	 */
	private $_rowLoaded = false;

	/**
	 * Constructor
	 */
	public function __construct(Database $db)
	{
		// Get the database connection
		$this->_conn = $db->getConnection($this->_database);
	}

	/**
	 * Get the value of a column
	 *
	 * @param string $key
	 */
	public function __get($key)
	{
		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}
		return null;
	}

	/**
	 * Set the value of a column
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		if (isset($this->_data[$key])) {
			$this->_data[$key] = $value;
		}
	}

	/**
	 * Load a row from the database
	 *
	 * @param mixed $id
	 */
	public function loadRow($id)
	{
		// Check that primary key matches id
		if (is_null($this->_primary)) {
			throw new Model\Exception('Unable to load a row from a table without a primary key.');
		}
		// Check for multiple keys
		if (is_array($this->_primary)) {
			if (!is_array($id) || count($this->_primary) != count($id)) {
				throw new Model\Exception('The format of the id must match the primary key.');
			}
			$priKey = array();
			foreach ($id as $key => $value) {
				$priKey[] = $this->_primary[$key] . ' = ' . $this->_fmtValue($value);
			}
			$where = implode(' and ', $priKey);
		} else {
			$where = $this->_primary . ' = ' . $this->_fmtValue($id);
		}
		// Query database
		$res = $this->_conn->query('select * from ' . $this->_table . ' where ' . $where);
		// Process result
		if ($res !== false) {
			if ($res->num_rows == 1) {
				$this->_data = $res->fetch_assoc();
				$this->_rowLoaded = true;
				return true;
			} elseif ($res->num_rows > 1) {
				throw new Model\Exception('Invalid table definition, more than 1 rows were returned.');
			}
		}
		return false;
	}

	/**
	 * Format a value for SQL query
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function _fmtValue($value)
	{
		if (is_numeric($value)) {
			return (string) $value;
		} else {
			return '\'' . $this->_conn->real_escape_string($value) . '\'';
		}
	}

}