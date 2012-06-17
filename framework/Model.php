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
	 * Cache module
	 *
	 * @var Cache
	 */
	protected $_cache;

	/**
	 * Cache lifetime
	 *
	 * @var int
	 */
	protected $_cacheLifetime;

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
	 * @var string | array
	 */
	protected $_autoIncrement = null;

	/**
	 * Data contained within a row
	 *
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Cache prefix
	 *
	 * @var string
	 */
	const CACHE_PREFIX = 'Model';

	/**
	 * Mapping modes
	 *
	 * @var int
	 */
	const MAPPING_ANY    = 0;
	const MAPPING_SINGLE = 1;
	const MAPPING_MULTI  = 2;

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
	 * Get the value of a column
	 *
	 * @param string $key
	 */
	public function &__get($key)
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
	public function setVal($key, $value)
	{
		if (isset($this->_data[$key])) {
			$this->_data[$key] = $value;
		}
	}

	/**
	 * Get the value of a column
	 *
	 * @param string $key
	 */
	public function &getVal($key)
	{
		if (isset($this->_data[$key])) {
			return $this->_data[$key];
		}
		return null;
	}

	/**
	 * Set row data
	 *
	 * @param array $data
	 * @return void
	 */
	public function setData($data = array())
	{
		if (is_array($data) && count($data)) {
			$this->_data = $data;
		}
	}

	/**
	 * Get row data
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Set cache module
	 *
	 * @param Cache $cache
	 */
	public function setCache($cache)
	{
		$this->_cache = $cache;
	}

	/**
	 * Set cache lifetime
	 *
	 * @param int $lifetime
	 */
	public function setCacheLifetime($lifetime)
	{
		if (is_numeric($lifetime)) {
			$this->_cacheLifetime = $lifetime;
		}
	}

	/**
	 * Clear an entry in the cache matching the id
	 *
	 * @param mixed $id
	 * @return bool
	 */
	public function clearCache($id)
	{
		if (is_object($this->_cache)) {
			// Cache key
			if (is_array($id)) {
				$id = implode(':', $id);
			}
			$key = self::CACHE_PREFIX . ':' . get_class($this) . ':' . $id;
			// Delete key
			return $this->_cache->delete($key);
		}
		return false;
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
		// Get data
		$data = $this->_getData('select * from ' . $this->_table . ' where ' . $this->_fmtPrimary($id), $id);
		// If null, not found
		if (is_null($data)) {
			return false;
		}
		// Map data to object
		$this->_mapData($data, self::MAPPING_SINGLE);
		return true;
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
					'insert into ' . $this->_table . ' (' . implode(', ', array_keys($this->_data)) .
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
			// Delete related cache keys
			if (is_array($this->_primary) && count($missing) == 0) {
				$id = array();
				foreach ($this->_primary as $value) {
					$id[] = $this->_data[$value];
				}
				$this->clearCache($id);
			} elseif (isset($this->_data[$this->_primary])) {
				$this->clearCache($this->_data[$this->_primary]);
			}
			// Check for insert id and update autoincremented field
			if ($this->_conn->insert_id != 0 && !is_null($this->_autoIncrement)) {
				$this->_data[$this->_autoIncrement] = $this->_conn->insert_id;
			}
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
		if (is_null($id)) {
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
			} else {
				if (!isset($this->_data[$this->_primary])) {
					throw new Model\Exception('Unable to delete a row without a value for the primary key.');
				}
				$id = $this->_data[$this->_primary];
			}
		}
		$where = $this->_fmtPrimary($id);
		// Delete row
		$res = $this->_conn->query('delete from ' . $this->_table . ' where ' . $where);
		// Process result
		if ($res !== false && $this->_conn->affected_rows) {
			// Delete related cache keys
			$this->clearCache($id);
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
	 * @param string $cacheKey
	 * @return array
	 */
	public function find($where = null, $order = null, $limit = null, $cacheKey = null) {
		// Get data
		$data = $this->_getData(
			'select * from ' . $this->_table . $this->_fmtWhere($where) .
			$this->_fmtOrder($order) . $this->_fmtLimit($limit),
			$cacheKey
		);
		// Map data to object(s)
		return $this->_mapData($data, self::MAPPING_MULTI);
	}

	/**
	 * Set autocommit state
	 *
	 * @param boolean $state
	 */
	public function autocommit($state = true)
	{
		$res = $this->_conn->autocommit($state);
		if ($res === false) {
			throw new Model\Exception('Failed to set autocommit state.');
		}
	}

	/**
	 * Commit current transaction
	 */
	public function commit() {
		$res = $this->_conn->commit();
		if ($res === false) {
			throw new Model\Exception('Commit transaction failed.');
		}
	}

	/**
	 * Rollback last transaction
	 */
	public function rollback() {
		$res = $this->_conn->rollback();
		if ($res === false) {
			throw new Model\Exception('Rollback transation failed.');
		}
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
		if (is_string($value)) {
			return '\'' . $this->_conn->real_escape_string($value) . '\'';
		} elseif (is_numeric($value)) {
			return (string) $value;
		} elseif (is_null($value)) {
			return 'null';
		}
	}

	/**
	 * Format where for SQL query
	 *
	 * @param mixed $where
	 * @return string
	 */
	protected function _fmtWhere($where)
	{
		if (is_string($where)) {
			return ' where ' . $where;
		} elseif (is_array($where)) {
			$sql = ' where ';
			$first = true;
			foreach ($where as $field => $value) {
				if (!$first) {
					$sql .= ' and ';
				}
				// If key is a string, process as field/value pair
				if (is_string($field)) {
					// Array values add as in
					if (is_array($value)) {
						$sql .= $field . ' in (' . implode(', ', $value) . ')';
					// Other values add as =
					} else {
						$sql .= $field . ' = ' . $this->_fmtValue($value);
					}
				// If not just add to the statement
				} else {
					$sql .= $value;
				}
				$first = false;
			}
			return $sql;
		}
		return null;
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
			return ' order by ' . $order;
		} elseif (is_array($order)) {
			return ' order by ' . implode(', ', $order);
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
			return ' limit ' . $limit;
		} elseif (is_array($limit)) {
			return ' limit ' . current($limit) . ', ' . next($limit);
		}
		return null;
	}

	/**
	 * Get data for a query from cache or database
	 *
	 * @param string $sql
	 * @param mixed $id
	 * @return array
	 */
	protected function _getData($sql, $id = null, $cacheNull = false)
	{
		$data = false;
		// Check if cache is enabled
		if (is_object($this->_cache) && !is_null($id)) {
			// Cache key
			if (is_array($id)) {
				$id = implode(':', $id);
			}
			$key = self::CACHE_PREFIX . ':' . get_class($this) . ':' . $id;
			// Read from cache
			$data = $this->_cache->get($key);
		}
		// Query database is cache lookup unsuccessful
		if ($data === false) {
			$res = $this->_conn->query($sql);
			if ($res !== false && $res->num_rows) {
				$data = array();
				while (($row = $res->fetch_assoc()) !== null) {
					$data[] = $row;
				}
			} elseif ($cacheNull) {
				$data = null;
			} else {
				return null;
			}
		}
		// Save/update cache
		if (is_object($this->_cache) && !is_null($id)) {
			$this->_cache->set($key, $data, 0, $this->_cacheLifetime);
		}
		// Return data array
		return $data;
	}

	/**
	 * Provides ORM for data, expects data from _getData
	 *
	 * @param array $data
	 */
	protected function _mapData($data, $mode = self::MAPPING_ANY)
	{
		// Only works with an array
		if (!is_array($data)) {
			return null;
		}
		// If 1 row set to object
		if (count($data) == 1 && $mode != self::MAPPING_MULTI) {
			$this->setData(current($data));
			return $this;
		// If more than one create an array of new objects
		} elseif ($mode != self::MAPPING_SINGLE) {
			$resArray = array();
			$class = get_class($this);
			foreach ($data as $row) {
				$resObj = new $class($this->_conn);
				$resObj->setData($row);
				$resArray[] = $resObj;
			}
			return $resArray;
		// Data does not match the mapping format
		} else {
			throw new Model\Exception('Data is not in the correct format.');
		}
	}

}