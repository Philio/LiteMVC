<?php
/**
 * LiteMVC Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010 - 2012
 * @license GNU General Public License version 3
 * @package LiteMVC
 * @version 0.2.1
 */
namespace LiteMVC\Auth;

interface User
{

	/**
	 * Login using the provided username / password
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login($username, $password);

	/**
	 * Get the user id of the current user
	 *
	 * @return int
	 */
	public function getUserId();

}