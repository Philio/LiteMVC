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
	 * Auto increment field
	 *
	 * @var bool
	 */
	protected $_autoIncrement = null;

	/**
	 * Data contained within a row
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Constructor
	 *
	 * @param Database | object $conn
	 */
	public function __construct($conn)
	{
		if ($conn instanceof Database) {
			// Get the database connection
			$this->_conn = $conn->getConnection($this->_database);
		} else {
			$this->_conn = $conn;
		}
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
	 * Set model data
	 *
	 * @param array $data
	 * @return void
	 */
	public function set($data = array())
	{
		if (is_array($data) && count($data)) {
			$this->_data = $data;
		}
	}

	/**
	 * Load a row from the database
	 *
	 * @param mixed $id
	 */
	public function load($id)
	{
		// Check that primary key is set
		if (is_null($this->_primary)) {
			throw new Model\Exception('Unable to load a row from the table without a primary key.');
		}
		// Format primary key for where statement
		$where = $this->_fmtPrimary($id);
		// Query database
		$res = $this->_conn->query('select * from ' . $this->_table . ' where ' . $where);
		// Process result
		if ($res !== false) {
			if ($res->num_rows == 1) {
				$this->_data = $res->fetch_assoc();
				return true;
			} elseif ($res->num_rows > 1) {
				throw new Model\Exception('Invalid table definition, more than 1 rows were returned.');
			}
		}
		return false;
	}

	/**
	 * Save the loaded row or current data set
	 *
	 * @return bool
	 */
	public function save()
	{
		// Check that primary key is set
		if (is_null($this->_primary)) {
			throw new Model\Exception('Unable to save a row to the table without a primary key.');
		}
		// Sort data
		$values = array();
		$pairs = array();
		foreach ($this->_data as $key => $value) {
			// Used for inserts
			$values[] = $this->_fmtValue($value);
			// Used for updates (primary key is ignored)
			if ($key != $this->_primary || (is_array($this->_primary) && !in_array($key, $this->_primary))) {
				$pairs[] = $key  . ' = ' . $this->_fmtValue($value);
			}
		}
		// Check for multiple keys
		if (is_array($this->_primary)) {
			// Check if keys are set
			$missing = array();
			foreach ($this->_primary as $value) {
				if (!isset($this->_data[$value])) {
					$missing[] = $value;
				}
			}
			// If keys found or only missing key is autoincremented
			if (count($missing) == 0 || (count($missing) == 1 && current($missing) == $this->_autoIncrement)) {
				// Insert/update
				$res = $this->_conn->query(
					'insert into ' . $this->_table . ' (' . implode(', ', array_keys($this->_data)) .
					') values (' . implode(', ', $values) . ') on duplicate key update ' .
					implode(', ', $pairs)
				);
			} else {
				throw new Model\Exception('Unable to save a row to the table without a valid primary key.');
			}
		} else {
			// If primary key is set and autoincremented update
			if (isset($this->_data[$this->_primary]) && $this->_primary == $this->_autoIncrement) {
				$res = $this->_conn->query(
					'update ' . $this->_table . ' set ' . implode(', ', $pairs) . ' where ' .
					$this->_primary . ' = ' . $this->_data[$this->_primary]
				);
			// If primary key isn't set and autoincremented insert
			} elseif (!isset($this->_data[$this->_primary]) && $this->_primary == $this->_autoIncrement) {
				$res = $this->_conn->query(
					'insert into ' . $this->_table . ' ( ' . implode(', ', array_keys($this->_data)) .
					') values (' . implode(', ', $values) . ')'
				);
			// Is set and not autoincremented insert with dupe key update
			} elseif (isset($this->_data[$this->_primary]) && $this->_primary != $this->_autoIncrement) {
				// Insert/update
				$res = $this->_conn->query(
					'insert into ' . $this->_table . ' (' . implode(', ', array_keys($this->_data)) .
					') values (' . implode(', ', $values) . ') on duplicate key update ' .
					implode(', ', $pairs)
				);
			} else {
				throw new Model\Exception('Unable to save a row to the table, insufficient data.');
			}
		}
		// Process result
		if ($res !== false && $this->_conn->affected_rows) {
			return true;
		}
		return false;
	}

	/**
	 * Delete the loaded row or a row specified by id
	 *
	 * @param mixed $id
	 * @return bool
	 */
	public function delete($id = null)
	{
		// Check that primary key is set
		if (is_null($this->_primary)) {
			throw new Model\Exception('Unable to delete a row from a table without a primary key.');
		}
		// If id is set delete row specified by id
		if (!is_null($id)) {
			// Format primary key for where statement
			$where = $this->_fmtPrimary($id);
			// Delete row
			$res = $this->_conn->query('delete from ' . $this->_table . ' where ' . $where);
			// Process result
			if ($res !== false && $this->_conn->affected_rows) {
				return true;
			}
			return false;
		}
		// If id isn't set check row data
		if (is_array($this->_primary)) {
			// Determine id from row data
			$id = array();
			foreach ($this->_primary as $value) {
				if (isset($this->_data[$value])) {
					$id[] = $this->_data[$value];
				} else {
					throw new Model\Exception('Unable to delete a row without a value for the primary key.');
				}
			}
			$where = $this->_fmtPrimary($id);
		} else {
			if (!isset($this->_data[$this->_primary])) {
				throw new Model\Exception('Unable to delete a row without a value for the primary key.');
			}
			$where = $this->_fmtPrimary($this->_data[$this->_primary]);
		}
		// Delete row
		$res = $this->_conn->query('delete from ' . $this->_table . ' where ' . $where);
		// Process result
		if ($res !== false && $this->_conn->affected_rows) {
			return true;
		}
		return false;
	}

	/**
	 * Find records in the table
	 *
	 * @param mixed $where
	 * @param mixed $order
	 * @param mixed $limit
	 * @return array
	 */
	public function find($where = null, $order = null, $limit = null) {

	}

	/**
	 * Format a primary key for SQL query
	 * 
	 * @param mixed $id
	 * @return string
	 */
	protected function _fmtPrimary($id)
	{
		// Check for multiple keys
		if (is_array($this->_primary)) {
			if (!is_array($id) || count($this->_primary) != count($id)) {
				throw new Model\Exception('The format of the id must match the primary key.');
			}
			$priKey = array();
			foreach ($id as $key => $value) {
				$priKey[] = $this->_primary[$key] . ' = ' . $this->_fmtValue($value);
			}
			return implode(' and ', $priKey);
		} else {
			return $this->_primary . ' = ' . $this->_fmtValue($id);
		}
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

	protected function _fmtWhere($where)
	{

	}

	/**
	 * Format order for SQL query
	 *
	 * @param mixed $order
	 * @return string
	 */
	protected function _fmtOrder($order)
	{
		if (is_string($order)) {
			return 'order by ' . $order;
		} elseif (is_array($order)) {
			return 'order by ' . implode(', ', $order);
		}
		return null;
	}

	/**
	 * Format limit for SQL query
	 *
	 * @param mixed $limit
	 * @return string
	 */
	protected function _fmtLimit($limit)
	{
		if (is_numeric($limit) || is_string($limit)) {
			return 'limit ' . $limit;
		} elseif (is_array($limit)) {
			return 'limit ' . current($limit) . ', ' . next($limit);
		}
		return null;
	}

}