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

interface Acl
{
	
	/**
	 * Get permissions for a user
	 * 
	 * @param int $userId
	 * @return array
	 */
	public function getPermissions($userId);

}