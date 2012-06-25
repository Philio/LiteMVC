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

	public function login($username, $password)
	{

	}

	public function getUserId()
	{

	}

}
