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
namespace LiteMVC\Authenticate;

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