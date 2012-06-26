<?php
namespace Test\Model;

class User extends \LiteMVC\Model implements \LiteMVC\Auth\User
{

	/**
	 * Database name
	 *
	 * @var string
	 */
	protected $_database = 'test';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $_table = 'users';

	/**
	 * The primary key for the table
	 *
	 * @var mixed
	 */
	protected $_primary = 'id';

	/**
	 * Auto increment field
	 *
	 * @var string | array
	 */
	protected $_autoIncrement = 'id';

	/**
	 * Login using the provided username / password
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login($username, $password)
	{
		// Get data
		$data = $this->_getData(
			'select * from ' . $this->_table . " where username = '$username' and password = sha1('$password')",
			'login:' . $username
		);
		// If null, not found
		if (is_null($data)) {
			return false;
		}
		// Map data to object
		$this->_mapData($data, self::MAPPING_SINGLE);
		return true;
	}

	/**
	 * Get the user id of the current user
	 *
	 * @return int
	 */
	public function getUserId()
	{
		return $this->getVal('id');
	}

}
